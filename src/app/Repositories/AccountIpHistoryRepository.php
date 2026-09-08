<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

/**
 * Gathers the IP addresses an account is known to have used. The information is scattered
 * across a number of tables, none of which is a dedicated IP history, so this repository
 * consolidates them into a single, chronologically ordered view.
 */
class AccountIpHistoryRepository
{
    /**
     * Retrieves the IP addresses associated with the specified account, most recently seen first.
     *
     * @return array<int, array{ip: string, last_seen: string|null, number_of_occurrences: int, sources: array<int, string>}>
     */
    public function getIpHistory(int $accountId): array
    {
        $occurrences = array_merge(
            $this->getSystemErrorOccurrences($accountId),
            $this->getSecurityEventOccurrences($accountId),
            $this->getSessionOccurrences($accountId)
        );

        $history = [];
        foreach ($occurrences as $occurrence) {
            $ip = $occurrence['ip'];
            if ($ip === null || $ip === '') {
                continue;
            }

            if (! isset($history[$ip])) {
                $history[$ip] = [
                    'ip' => $ip,
                    'last_seen' => $occurrence['last_seen'],
                    'number_of_occurrences' => 0,
                    'sources' => [],
                ];
            }

            $history[$ip]['number_of_occurrences'] += $occurrence['number_of_occurrences'];
            $history[$ip]['sources'][] = $occurrence['source'];

            if ($occurrence['last_seen'] !== null &&
                ($history[$ip]['last_seen'] === null || $occurrence['last_seen'] > $history[$ip]['last_seen'])) {
                $history[$ip]['last_seen'] = $occurrence['last_seen'];
            }
        }

        $history = array_values($history);
        usort($history, fn ($a, $b) => strcmp($b['last_seen'] ?? '', $a['last_seen'] ?? ''));

        return $history;
    }

    /**
     * Retrieves just the IP addresses associated with the specified account.
     *
     * @return array<int, string>
     */
    public function getIpAddresses(int $accountId): array
    {
        return array_column($this->getIpHistory($accountId), 'ip');
    }

    /**
     * @return array<int, array{ip: string, last_seen: string|null, number_of_occurrences: int, source: string}>
     */
    private function getSystemErrorOccurrences(int $accountId): array
    {
        $rows = DB::table('system_errors')
            ->select([
                DB::raw('CAST(ip AS CHAR) AS ip'),
                DB::raw('MAX(created_at) AS last_seen'),
                DB::raw('COUNT(*) AS number_of_occurrences'),
            ])
            ->where('account_id', $accountId)
            ->whereNotNull('ip')
            ->groupBy('ip')
            ->get();

        return $this->toOccurrences($rows, 'exception-log');
    }

    /**
     * @return array<int, array{ip: string, last_seen: string|null, number_of_occurrences: int, source: string}>
     */
    private function getSecurityEventOccurrences(int $accountId): array
    {
        $rows = DB::table('account_security_events')
            ->select([
                DB::raw('ip_address AS ip'),
                DB::raw('MAX(created_at) AS last_seen'),
                DB::raw('COUNT(*) AS number_of_occurrences'),
            ])
            ->where('account_id', $accountId)
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->get();

        return $this->toOccurrences($rows, 'security-event');
    }

    /**
     * @return array<int, array{ip: string, last_seen: string|null, number_of_occurrences: int, source: string}>
     */
    private function getSessionOccurrences(int $accountId): array
    {
        $rows = DB::table('sessions')
            ->select([
                DB::raw('ip_address AS ip'),
                DB::raw('MAX(last_activity) AS last_activity'),
                DB::raw('COUNT(*) AS number_of_occurrences'),
            ])
            ->where('user_id', $accountId)
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->get();

        return $rows->map(fn ($row) => [
            'ip' => (string) $row->ip,
            'last_seen' => $row->last_activity !== null
                ? date('Y-m-d H:i:s', intval($row->last_activity))
                : null,
            'number_of_occurrences' => intval($row->number_of_occurrences),
            'source' => 'session',
        ])->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return array<int, array{ip: string, last_seen: string|null, number_of_occurrences: int, source: string}>
     */
    private function toOccurrences($rows, string $source): array
    {
        return $rows->map(fn ($row) => [
            'ip' => (string) $row->ip,
            'last_seen' => $row->last_seen !== null ? (string) $row->last_seen : null,
            'number_of_occurrences' => intval($row->number_of_occurrences),
            'source' => $source,
        ])->all();
    }
}
