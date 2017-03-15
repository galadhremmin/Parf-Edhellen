<?php

    namespace App\Repositories;

    use Carbon\Carbon;
    use Illuminate\Support\Facades\DB;
    use App\Models\Translation;
    use App\Models\TranslationReview;

    class TranslationReviewRepository extends RepositoryBase
    {
        public function __construct(TranslationReview $model)
        {
            $this->model = $model;
        }

        public function getRecentlyApproved($numberOfRecords = 10)
        {
            $entities = DB::table('translation_review as tr')
                ->join('translation as t', function ($join) {
                    $join->on('tr.TranslationID', '=', 't.TranslationID')
                        ->orOn('tr.TranslationID', '=', 't.EldestTranslationID');
                })
                ->join('auth_accounts', 'tr.AuthorID', '=', 'auth_accounts.AccountID')
                ->join('word', 't.WordID', '=', 'word.KeyID')
                ->where('tr.Approved', 1)
                ->where('t.Deleted', 0)
                ->where('t.Latest', 1)
                ->orderBy('tr.DateCreated', 'desc')
                ->limit($numberOfRecords)
                ->select('tr.AuthorID', 't.LanguageID', 't.DateCreated', 'Key as Word', 't.TranslationID', 'Nickname as AuthorName')
                ->get();

            foreach ($entities as $entity)
                $entity->DateCreated = Carbon::createFromFormat('Y-m-d H:i:s', $entity->DateCreated);

            return $entities;
        }
    }