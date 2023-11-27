<?php

namespace App\Jobs;

use App\Models\Account;
use App\Models\Contribution;
use App\Models\FlashcardResult;
use App\Models\ForumDiscussion;
use App\Models\ForumPost;
use App\Models\ForumPostLike;
use App\Models\ForumThread;
use App\Models\GlossInflection;
use App\Models\Versioning\GlossVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MigrateAccountData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $_fromAccountId;
    /**
     * @var int
     */
    private $_toAccountId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $fromId, int $toId)
    {
        $this->_fromAccountId = $fromId;
        $this->_toAccountId = $toId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from = Account::findOrFail($this->_fromAccountId);
        $to = Account::findOrFail($this->_toAccountId);

        $this->moveContributions($from, $to);
        $this->moveFlashcardResults($from, $to);
        $this->moveDiscussActivity($from, $to);
        $this->moveDictionaryActivity($from, $to);
        $this->moveSentences($from, $to);
    }

    private function moveContributions(Account $from, Account $to)
    {
        Contribution::withoutTimestamps(function () use ($from, $to) {
            $from->contributions()->update([
                'account_id' => $to->id
            ]);

            Contribution::where('reviewed_by_account_id', $from->id)
                ->update([
                    'reviewed_by_account_id' => $to->id
                ]);
        });
    }

    private function moveFlashcardResults(Account $from, Account $to)
    {
        FlashcardResult::withoutTimestamps(function () use ($from, $to) {
            $from->flashcard_results()->update([
                'account_id' => $to->id
            ]);
        });
    }

    private function moveDiscussActivity(Account $from, Account $to)
    {
        ForumDiscussion::withoutTimestamps(function () use ($from, $to) {
            $from->forum_discussions()->update([
                'account_id' => $to->id
            ]);
        });
    
        ForumPostLike::withoutTimestamps(function () use ($from, $to) {
            $from->forum_post_likes()->update([
                'account_id' => $to->id
            ]);
        });
    
        ForumPost::withoutTimestamps(function () use ($from, $to) {
            $from->forum_posts()->update([
                'account_id' => $to->id
            ]);
        });

        ForumThread::withoutTimestamps(function () use ($from, $to) {
            $from->forum_threads()->update([
                'account_id' => $to->id
            ]);
        });
    }

    private function moveDictionaryActivity(Account $from, Account $to)
    {
        $from->gloss_inflections()->update([
            'account_id' => $to->id
        ]);

        GlossVersion::withoutTimestamps(function () use ($from, $to) {
            // Versions are a reflection of the past and shouldn't be updated. The rest
            // of the dictionary is fine to have newer `updated_at` timestamps.
            $from->gloss_versions()->update([
                'account_id' => $to->id
            ]);
        });

        $from->glosses()->update([
            'account_id' => $to->id
        ]);

        $from->words()->update([
            'account_id' => $to->id
        ]);
    }

    private function moveSentences(Account $from, Account $to)
    {
        $from->sentences()->update([
            'account_id' => $to->id
        ]);
    }
}
