<?php

namespace App\Adapters;

use App\Helpers\LinkHelper;
use App\Helpers\StorageHelper;
use App\Http\Discuss\ContextFactory;
use App\Interfaces\IMarkdownParser;
use App\Models\Account;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Repositories\ValueObjects\ForumThreadsForPostsValue;
use App\Repositories\ValueObjects\ForumThreadValue;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\Collection;

class DiscussAdapter
{
    private ContextFactory $_contextFactory;

    private StorageHelper $_storageHelper;

    private AuthManager $_authManager;

    private LinkHelper $_linkHelper;

    private IMarkdownParser $_markdownParser;

    public function __construct(ContextFactory $contextFactory, StorageHelper $storageHelper, AuthManager $authManager,
        LinkHelper $linkHelper, IMarkdownParser $markdownParser)
    {
        $this->_contextFactory = $contextFactory;
        $this->_storageHelper = $storageHelper;
        $this->_authManager = $authManager;
        $this->_linkHelper = $linkHelper;
        $this->_markdownParser = $markdownParser;
    }

    public function adaptAccount(Account $account)
    {
        if ($account->has_avatar) {
            $account->setAttribute('avatar_path', $this->_storageHelper->accountAvatar($account, true));
        }

        return $account;
    }

    public function adaptAccountsPerForumGroup(Collection $data)
    {
        foreach ($data as $accounts) {
            foreach ($accounts as $account) {
                $this->adaptAccount($account);
            }
        }

        return $data;
    }

    public function adaptPost(ForumPost $post)
    {
        if ($post->account_id) {
            $this->adaptAccount($post->account);
        }

        if ($post->is_hidden || $post->is_deleted) {
            $post->content = null;
        } else {
            $post->content = $this->_markdownParser->parseMarkdown($post->content);
        }

        return $post;
    }

    public function adaptPosts(Collection $posts)
    {
        $posts->map(function ($post, $i) {
            $this->adaptPost($post);
        });

        return $posts;
    }

    public function adaptThread(ForumThread $thread)
    {
        if ($thread->account !== null) {
            $this->adaptAccount($thread->account);
        }

        return $thread;
    }

    public function adaptForumThread($data)
    {
        $thread = null;
        if ($data instanceof ForumThreadValue) {
            $thread = $data->getThread();
        } elseif ($data instanceof ForumThread) {
            $thread = &$data;
        } else {
            throw new \Exception(sprintf('Unsupported entity %s.', get_class($data)));
        }

        $this->adaptThread($thread);

        return $data;
    }

    public function adaptThreads(Collection $threads)
    {
        $threads->map(function ($thread) {
            $this->adaptThread($thread);
        });

        return $threads;
    }

    public function adaptForTimeline(Collection $posts)
    {
        $user = $this->_authManager->user();
        $thread_id = 0;
        $inverted = true;

        $adapted = [];
        $i = 0;
        foreach ($posts as $post) {
            if ($thread_id !== $post->forum_thread_id) {
                $i = 0;

                $inverted = ! $inverted;
                $thread_id = $post->forum_thread_id;
            }

            $context = $this->_contextFactory->create($post->forum_thread->entity_type);
            if (! $context->available($post, $user)) {
                continue; // skip unavailable posts
            }

            $iconPath = $context->getIconPath();
            $adapted[] = (object) [
                'id' => $post->id,
                'forum_group_id' => $post->forum_thread->forum_group_id,
                'forum_thread_id' => $post->forum_thread_id,
                'subject' => $post->forum_thread->subject,
                'subject_path' => $post->forum_thread->normalized_subject,
                'icon' => $iconPath,
                'created_at' => $post->updated_at ?: $post->created_at,
                'content' => $post->content,
                'number_of_likes' => $post->number_of_likes,
                'i' => $i,
                'inverted' => $inverted,
            ];

            $i += 1;
        }

        return $adapted;
    }

    public function adaptForSearchResults(ForumThreadsForPostsValue $value)
    {
        $sections = [];
        foreach ($value->getThreads() as $t) {
            $thread = clone $t;

            $groupId = $thread->forum_group_id;
            $group = $value->getGroups()[$groupId];

            $author = new Account;
            $author->id = $thread->account_id;
            $author->has_avatar = $thread->has_avatar;
            $thread->account_avatar_path = $this->_storageHelper->accountAvatar($author);
            $thread->account_path = $this->_linkHelper->author($thread->account_id, $thread->account_name);

            $thread->thread_path = $this->_linkHelper->forumThread(
                $groupId, $group->name, $thread->id, $thread->normalized_subject,
                $thread->forum_post_id
            );

            if (isset($sections[$groupId])) {
                $sections[$groupId]['entities'][] = $thread;

            } else {
                $sections[$groupId] = [
                    'entities' => [$thread],
                    'language' => $group,
                ];
            }
        }

        return [
            'sections' => array_values($sections),
        ];
    }
}
