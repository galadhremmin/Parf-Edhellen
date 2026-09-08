<?php

namespace Tests\Unit\Helpers;

use App\Helpers\AgGridFilterHelper;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgGridFilterHelperTest extends TestCase
{
    private const EXPRESSIONS = [
        'createdAt' => 'created_at',
        'message' => 'message',
        'accountId' => 'account_id',
        'ip' => 'CAST(ip AS CHAR)',
    ];

    private AgGridFilterHelper $_helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_helper = new AgGridFilterHelper;
    }

    private function apply(?array $filterModel): array
    {
        $query = DB::table('system_errors');
        $this->_helper->apply($query, $filterModel, self::EXPRESSIONS);

        return [$query->toSql(), $query->getBindings()];
    }

    public function test_ignores_empty_and_unknown_fields()
    {
        [$sql] = $this->apply(null);
        $this->assertStringNotContainsString('where', $sql);

        [$sql] = $this->apply(['nonsense' => ['filterType' => 'text', 'type' => 'contains', 'filter' => 'x']]);
        $this->assertStringNotContainsString('where', $sql);
    }

    public function test_applies_text_conditions()
    {
        [$sql, $bindings] = $this->apply([
            'ip' => ['filterType' => 'text', 'type' => 'contains', 'filter' => '127.0.0.1'],
        ]);

        $this->assertStringContainsString('CAST(ip AS CHAR) LIKE ?', $sql);
        $this->assertSame(['%127.0.0.1%'], $bindings);

        [$sql, $bindings] = $this->apply([
            'ip' => ['filterType' => 'text', 'type' => 'equals', 'filter' => '127.0.0.1'],
        ]);

        $this->assertStringContainsString('CAST(ip AS CHAR) = ?', $sql);
        $this->assertSame(['127.0.0.1'], $bindings);
    }

    public function test_escapes_like_wildcards()
    {
        [, $bindings] = $this->apply([
            'message' => ['filterType' => 'text', 'type' => 'startsWith', 'filter' => '100%_of'],
        ]);

        $this->assertSame(['100\%\_of%'], $bindings);
    }

    public function test_applies_combined_conditions()
    {
        [$sql, $bindings] = $this->apply([
            'ip' => [
                'filterType' => 'text',
                'operator' => 'OR',
                'conditions' => [
                    ['filterType' => 'text', 'type' => 'equals', 'filter' => '127.0.0.1'],
                    ['filterType' => 'text', 'type' => 'startsWith', 'filter' => '10.'],
                ],
            ],
        ]);

        $this->assertStringContainsString('CAST(ip AS CHAR) = ? or CAST(ip AS CHAR) LIKE ?', $sql);
        $this->assertSame(['127.0.0.1', '10.%'], $bindings);
    }

    public function test_applies_legacy_numbered_conditions()
    {
        [$sql, $bindings] = $this->apply([
            'ip' => [
                'filterType' => 'text',
                'operator' => 'AND',
                'condition1' => ['filterType' => 'text', 'type' => 'startsWith', 'filter' => '10.'],
                'condition2' => ['filterType' => 'text', 'type' => 'endsWith', 'filter' => '.1'],
            ],
        ]);

        $this->assertStringContainsString('CAST(ip AS CHAR) LIKE ? and CAST(ip AS CHAR) LIKE ?', $sql);
        $this->assertSame(['10.%', '%.1'], $bindings);
    }

    public function test_applies_number_conditions()
    {
        [$sql, $bindings] = $this->apply([
            'accountId' => ['filterType' => 'number', 'type' => 'inRange', 'filter' => '1', 'filterTo' => 10],
        ]);

        $this->assertStringContainsString('account_id BETWEEN ? AND ?', $sql);
        $this->assertSame([1, 10], $bindings);

        [$sql] = $this->apply([
            'accountId' => ['filterType' => 'number', 'type' => 'greaterThan', 'filter' => 'not-a-number'],
        ]);

        $this->assertStringNotContainsString('account_id', $sql);
    }

    public function test_applies_date_conditions()
    {
        [$sql, $bindings] = $this->apply([
            'createdAt' => ['filterType' => 'date', 'type' => 'equals', 'dateFrom' => '2026-01-01 00:00:00'],
        ]);

        $this->assertStringContainsString('DATE(created_at) = DATE(?)', $sql);
        $this->assertSame(['2026-01-01 00:00:00'], $bindings);

        [$sql] = $this->apply([
            'createdAt' => ['filterType' => 'date', 'type' => 'inRange', 'dateFrom' => '2026-01-01 00:00:00'],
        ]);

        $this->assertStringNotContainsString('created_at', $sql);
    }

    public function test_applies_blank_conditions_according_to_filter_type()
    {
        [$sql] = $this->apply(['accountId' => ['filterType' => 'number', 'type' => 'blank']]);
        $this->assertStringContainsString('account_id IS NULL', $sql);
        $this->assertStringNotContainsString('= ?', $sql);

        [$sql, $bindings] = $this->apply(['message' => ['filterType' => 'text', 'type' => 'notBlank']]);
        $this->assertStringContainsString('message IS NOT NULL AND message <> ?', $sql);
        $this->assertSame([''], $bindings);
    }

    public function test_parses_filter_model()
    {
        $this->assertNull($this->_helper->parse(null));
        $this->assertNull($this->_helper->parse(''));
        $this->assertNull($this->_helper->parse('not json'));
        $this->assertSame(['ip' => ['type' => 'contains']], $this->_helper->parse('{"ip":{"type":"contains"}}'));
    }
}
