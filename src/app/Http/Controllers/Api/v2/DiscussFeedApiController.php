<?php

namespace App\Http\Controllers\Api\v2;

use Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Http\Controllers\Controller;
use App\Repositories\DiscussRepository;
use App\Helpers\LinkHelper;
use App\Adapters\{
    JsonFeedAdapter,
    RssFeedAdapter
};

class DiscussFeedApiController extends Controller 
{
    private $_discussRepository;
    private $_linkHelper;

    public function __construct(DiscussRepository $discussRepository, LinkHelper $linkHelper)
    {
        $this->_discussRepository = $discussRepository;
        $this->_linkHelper = $linkHelper;
    }

    public function getPosts(Request $request)
    {
        $account = $request->user();
        $accountId = $account !== null ? $account->id : 0;

        return Cache::remember(sprintf('ed.feed.discs.p.%d.%s', $accountId, $this->getFormat($request)), 
            5 * 60 /* seconds */, function () use($request, $account) {
            $threads = $this->_discussRepository->getLatestPosts($account);
            return $this->formatResponse($request, $threads, 'Latest posts');
        });
    }

    public function getPostsInGroup(Request $request, int $forumGroupId)
    {
        $account = $request->user();
        $accountId = $account !== null ? $account->id : 0;

        $group = $this->_discussRepository->getGroup($forumGroupId);
        return Cache::remember(sprintf('ed.feed.disc.ps.%d.%s.%d', $accountId, $this->getFormat($request), $group->id), 
            5 * 60 /* seconds */, function () use($request, $group, $account) {
            $threads = $this->_discussRepository->getLatestPosts($account, $group->id);
            return $this->formatResponse($request, $threads, sprintf('Latest posts in %s', $group->name));
        });
    }

    private function getFormat(Request $request)
    {
        switch ($request->input('format')) {
            case 'rss':
                return 'rss';

            case 'json':
            default:
                return 'json';
        }
    }

    private function formatResponse(Request $request, Collection $data, string $title)
    {
        $websiteUrl = config('app.url');
        $feedUrl = $request->fullUrl();

        $formatter = null;
        $itemFormatter = null;
        switch ($this->getFormat($request))
        {
            case 'rss':
                $formatter = new RssFeedAdapter($title, $websiteUrl, $feedUrl, $title);
                $domain = parse_url(config('app.url'))['host'];
                $itemFormatter = function ($d) use($domain) {
                    // Please refer to `RssFeedAdapter` for expected properties. 
                    $url = $this->_linkHelper->forumThread($d->forum_thread->forum_group_id,
                        'g', $d->forum_thread_id, $d->forum_thread->normalized_subject, $d->id);
                    return [
                        'author' => sprintf('%s@%s (%s)', $d->account->nickname, $domain, $d->account->nickname),
                        'title' => $d->forum_thread->subject,
                        'description' => trim($d->content),
                        'link' => $url,
                        'pubDate' => $d->created_at->toRfc1123String(),
                        'guid' => $url
                    ];
                };
                break;
            case 'json':
            default: 
                $formatter = new JsonFeedAdapter($title, $websiteUrl, $feedUrl);
                $itemFormatter = function ($d) {
                    // Please refer to `JsonFeedAdapter` for expected properties.
                    return [
                        'id' => $d->id,
                        'title' => $d->forum_thread->subject,
                        'content_text' => trim($d->content),
                        'url' => $this->_linkHelper->forumThread($d->forum_thread->forum_group_id,
                            'g', $d->forum_thread_id, $d->forum_thread->normalized_subject,
                            $d->id),
                        'date_published' => $d->created_at->toRfc3339String(),
                        'date_modified' => ($d->updated_at ?: $d->created_at)->toRfc3339String(),
                        'author' => [
                            'name' => $d->account->nickname,
                            'url' => $this->_linkHelper->author($d->account_id, $d->account->nickname)
                        ]
                    ];
                };
                break;
        }

        return $formatter->adapt($data, $itemFormatter);
    }
}
