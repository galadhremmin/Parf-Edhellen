<?php

namespace App\Adapters;

use Illuminate\Support\Collection;

class QueueJobStatisticAdapter
{
    public function adapt(Collection $stats): Collection
    {
        return $stats->map(fn ($item) => $this->adaptOne($item));
    }

    private function adaptOne(object $item): object
    {
        $total   = (int) ($item->total_count ?? 0);
        $success = (int) ($item->success_count ?? 0);
        $failed  = (int) ($item->failed_count ?? 0);
        $retried = (int) ($item->retry_count ?? 0);

        return (object) [
            'queue'       => $item->queue_name,
            'total'       => $total,
            'success'     => $success,
            'failed'      => $failed,
            'retried'     => $retried,
            'successPct'  => $total > 0 ? round(($success / $total) * 100, 1) : 0,
            'failedPct'   => $total > 0 ? round(($failed  / $total) * 100, 1) : 0,
            'avgDisplay'  => $this->formatMs($item->avg_execution_time_ms ?? 0),
            'maxDisplay'  => $this->formatMs($item->max_execution_time_ms ?? 0),
            'minDisplay'  => $this->formatMs($item->min_execution_time_ms ?? 0),
        ];
    }

    private function formatMs(float $ms): string
    {
        $ms = round($ms);
        return $ms >= 1000 ? round($ms / 1000, 2).'s' : $ms.'ms';
    }
}
