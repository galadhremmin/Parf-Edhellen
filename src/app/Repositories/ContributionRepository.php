<?php

    namespace App\Repositories;

    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use App\Models\Contribution;

    class ContributionRepository
    {
        public function getRecentlyApproved($numberOfRecords = 10)
        {
            $entities = DB::table('contributions as tr')
                ->join('translations as t', function ($join) {
                    $join->on('tr.translation_id', '=', 't.id')
                        ->orOn('tr.translation_id', '=', 't.origin_translation_id');
                })
                ->join('accounts', 'tr.account_id', '=', 'accounts.id')
                ->join('words', 't.word_id', '=', 'words.id')
                ->where('tr.is_approved', 1)
                ->where('t.is_deleted', 0)
                ->where('t.is_latest', 1)
                ->orderBy('tr.created_at', 'desc')
                ->limit($numberOfRecords)
                ->select('tr.account_id', 't.language_id', 't.created_at', 'words.word', 't.id as translation_id', 'Nickname as account_name')
                ->get();

            return $entities;
        }
    }
