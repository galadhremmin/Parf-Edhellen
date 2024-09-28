<?php

use Carbon\Carbon;
use App\Models\AccountFeed;
use App\Models\AccountFeedRefreshTime;
use App\Models\ForumPost;
use App\Models\Gloss;
use App\Models\Initialization\Morphs;
use App\Models\Versioning\GlossVersion;
use App\Repositories\AccountFeedRepository;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        AccountFeed::where('content_type', Morphs::getAlias(Gloss::class))->delete();
        AccountFeedRefreshTime::where('feed_content_type', Morphs::getAlias(Gloss::class))->delete();

        $accountIds = GlossVersion::select('account_id')
            ->distinct()
            ->union(
                ForumPost::where('created_at', '>', Carbon::now()->add(-1, 'year'))
                    ->select('account_id')
                    ->distinct()
            )
            ->get()
            ->pluck('account_id')
            ->unique()
            ->sort();
        
        $repository = resolve(AccountFeedRepository::class);
        foreach ($accountIds as $accountId) {
            // warm up accounts with contributions to the dictionary or activity in Discuss.
            // Their feeds are probably a bit larger.
            $repository->generateForAccountId($accountId);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No restoration possible
    }
};
