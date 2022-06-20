<?php

namespace App\Http\Controllers\Api\v2;

use Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use App\Http\Controllers\Abstracts\Controller;
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
        $posts = $this->caching($request, 'ed.feed.d.ps', function ($account) {
            return $this->_discussRepository->getLatestPosts($account);
        });

        return $this->formatResponse($request, $posts, 'Latest posts');
    }

    public function getPostsInGroup(Request $request, int $forumGroupId)
    {
        $group = $this->_discussRepository->getGroup($forumGroupId);
        $posts = $this->caching($request, 'ed.feed.d.p.'.$group->id, function ($account) use($group) {
            return  $this->_discussRepository->getLatestPosts($account, $group->id);
        });

        return $this->formatResponse($request, $posts, sprintf('Latest posts in %s', $group->name));
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

    private function caching(Request $request, string $cacheKey, \Closure $func)
    {
        $account = $request->user();
        if ($account !== null) {
            return $func($account);
        }

        $format = $this->getFormat($request);
        return Cache::remember($cacheKey.'.'.$format, 5 * 60, function () use($account, $func) {
            return $func($account);
        });
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
                $contentType = 'application/rss+xml; charset=utf-8';
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
                $contentType = 'application/json; charset=utf-8';
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
                            'name' => $d->account ? $d->account->nickname : 'unknown',
                            'url' => $this->_linkHelper->author($d->account_id, $d->account ? $d->account->nickname : 'unknown')
                        ]
                    ];
                };
                break;
        }

        return response($formatter->adapt($data, $itemFormatter))
            ->header('Content-Type', $contentType);
    }
}
