<?php

namespace App\Helpers;

use Illuminate\Contracts\Database\Query\Builder;

/**
 * Translates an ag-Grid filter model into SQL conditions. The infinite row model does not filter
 * rows on the client, but forwards its filter model to the datasource, which makes it possible to
 * apply the filter to _every_ record rather than just the ones that happen to be loaded.
 *
 * Only fields present in the column map are considered, and the map's values are used verbatim as
 * SQL expressions, so the map must never be derived from user input.
 */
class AgGridFilterHelper
{
    /**
     * Applies the specified filter model to the specified query.
     *
     * @param  array<string, mixed>|null  $filterModel  the filter model, keyed by grid field name
     * @param  array<string, string>  $expressionsByField  grid field name => SQL expression
     */
    public function apply(Builder $query, ?array $filterModel, array $expressionsByField): void
    {
        if (empty($filterModel)) {
            return;
        }

        foreach ($filterModel as $field => $filter) {
            if (! isset($expressionsByField[$field]) || ! is_array($filter)) {
                continue;
            }

            $expression = $expressionsByField[$field];
            $query->where(function (Builder $query) use ($expression, $filter) {
                $this->applyFilter($query, $expression, $filter);
            });
        }
    }

    /**
     * Parses the specified JSON encoded filter model.
     *
     * @return array<string, mixed>|null
     */
    public function parse(?string $json): ?array
    {
        if ($json === null || $json === '') {
            return null;
        }

        $filterModel = json_decode($json, true);

        return is_array($filterModel) ? $filterModel : null;
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function applyFilter(Builder $query, string $expression, array $filter): void
    {
        // A filter with multiple conditions is expressed either as a list of conditions, or -- for
        // backwards compatibility with older versions of ag-Grid -- as numbered properties.
        $conditions = $filter['conditions'] ?? array_values(array_filter([
            $filter['condition1'] ?? null,
            $filter['condition2'] ?? null,
        ]));

        if (empty($conditions)) {
            $this->applyCondition($query, $expression, $filter, 'and');

            return;
        }

        $boolean = strtolower($filter['operator'] ?? 'AND') === 'or' ? 'or' : 'and';
        foreach ($conditions as $condition) {
            if (is_array($condition)) {
                $this->applyCondition($query, $expression, $condition, $boolean);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $condition
     */
    private function applyCondition(Builder $query, string $expression, array $condition, string $boolean): void
    {
        $type = $condition['type'] ?? null;
        if ($type === null) {
            return;
        }

        $filterType = $condition['filterType'] ?? 'text';

        if ($type === 'blank') {
            // An empty string only counts as blank for textual columns.
            $query->whereRaw($filterType === 'text'
                ? '('.$expression.' IS NULL OR '.$expression.' = ?)'
                : $expression.' IS NULL', $filterType === 'text' ? [''] : [], $boolean);

            return;
        }

        if ($type === 'notBlank') {
            $query->whereRaw($filterType === 'text'
                ? '('.$expression.' IS NOT NULL AND '.$expression.' <> ?)'
                : $expression.' IS NOT NULL', $filterType === 'text' ? [''] : [], $boolean);

            return;
        }

        switch ($filterType) {
            case 'number':
                $this->applyNumberCondition($query, $expression, $type, $condition, $boolean);
                break;

            case 'date':
                $this->applyDateCondition($query, $expression, $type, $condition, $boolean);
                break;

            default:
                $this->applyTextCondition($query, $expression, $type, $condition, $boolean);
                break;
        }
    }

    /**
     * @param  array<string, mixed>  $condition
     */
    private function applyTextCondition(Builder $query, string $expression, string $type, array $condition, string $boolean): void
    {
        $value = $condition['filter'] ?? null;
        if ($value === null || $value === '') {
            return;
        }

        $value = (string) $value;
        $escaped = $this->escapeLikeValue($value);

        switch ($type) {
            case 'equals':
                $query->whereRaw($expression.' = ?', [$value], $boolean);
                break;

            case 'notEqual':
                $query->whereRaw('('.$expression.' IS NULL OR '.$expression.' <> ?)', [$value], $boolean);
                break;

            case 'startsWith':
                $query->whereRaw($expression.' LIKE ?', [$escaped.'%'], $boolean);
                break;

            case 'endsWith':
                $query->whereRaw($expression.' LIKE ?', ['%'.$escaped], $boolean);
                break;

            case 'notContains':
                $query->whereRaw('('.$expression.' IS NULL OR '.$expression.' NOT LIKE ?)', ['%'.$escaped.'%'], $boolean);
                break;

            case 'contains':
            default:
                $query->whereRaw($expression.' LIKE ?', ['%'.$escaped.'%'], $boolean);
                break;
        }
    }

    /**
     * @param  array<string, mixed>  $condition
     */
    private function applyNumberCondition(Builder $query, string $expression, string $type, array $condition, string $boolean): void
    {
        $from = $condition['filter'] ?? null;
        $to = $condition['filterTo'] ?? null;

        if (! is_numeric($from)) {
            return;
        }

        $from = $from + 0;

        if ($type === 'inRange') {
            if (! is_numeric($to)) {
                return;
            }

            $query->whereRaw($expression.' BETWEEN ? AND ?', [$from, $to + 0], $boolean);

            return;
        }

        $operator = $this->toComparisonOperator($type);
        if ($operator === null) {
            return;
        }

        if ($operator === '<>') {
            $query->whereRaw('('.$expression.' IS NULL OR '.$expression.' <> ?)', [$from], $boolean);

            return;
        }

        $query->whereRaw($expression.' '.$operator.' ?', [$from], $boolean);
    }

    /**
     * @param  array<string, mixed>  $condition
     */
    private function applyDateCondition(Builder $query, string $expression, string $type, array $condition, string $boolean): void
    {
        $from = $condition['dateFrom'] ?? null;
        $to = $condition['dateTo'] ?? null;

        if (! is_string($from) || $from === '') {
            return;
        }

        if ($type === 'inRange') {
            if (! is_string($to) || $to === '') {
                return;
            }

            $query->whereRaw($expression.' BETWEEN ? AND ?', [$from, $to], $boolean);

            return;
        }

        // ag-Grid's date filter is expressed with day precision, whereas the columns it filters on
        // are timestamps, so equality is matched against the entire day.
        if ($type === 'equals' || $type === 'notEqual') {
            $negation = $type === 'notEqual' ? 'NOT ' : '';
            $query->whereRaw($negation.'DATE('.$expression.') = DATE(?)', [$from], $boolean);

            return;
        }

        $operator = $this->toComparisonOperator($type);
        if ($operator === null) {
            return;
        }

        $query->whereRaw($expression.' '.$operator.' ?', [$from], $boolean);
    }

    private function toComparisonOperator(string $type): ?string
    {
        return match ($type) {
            'equals' => '=',
            'notEqual' => '<>',
            'lessThan', 'before' => '<',
            'lessThanOrEqual' => '<=',
            'greaterThan', 'after' => '>',
            'greaterThanOrEqual' => '>=',
            default => null,
        };
    }

    private function escapeLikeValue(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $value);
    }
}
