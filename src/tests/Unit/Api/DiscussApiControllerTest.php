<?php

namespace Tests\Unit\Api;

use App\Models\{
    ForumGroup,
    ForumPost,
    ForumThread,
    ForumDiscussion,
};
use App\Models\Initialization\Morphs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DiscussApiControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_groups()
    {
        $response = $this->getJson(route('api.discuss.groups'));
        $response->assertSuccessful();
    }

    public function test_group()
    {
        $response = $this->getJson(route('api.discuss.group', ['groupId' => 0]));
        $response->assertNotFound();

        $group = ForumGroup::first();
        $response = $this->getJson(route('api.discuss.group', ['groupId' => $group->id]));
        $response->assertSuccessful();

        $this->assertTrue(isset($response['group']));
        $this->assertTrue($response['group']['id'] == $group->id);
    }

    public function test_threads()
    {
        $response = $this->getJson(route('api.discuss.threads'));
        $response->assertSuccessful();
    }

    public function test_thread()
    {
        $response = $this->getJson(route('api.discuss.thread', ['threadId' => 0]));
        $response->assertNotFound();

        $thread = ForumThread::first();
        $response = $this->getJson(route('api.discuss.thread', ['threadId' => $thread->id]));
        $response->assertSuccessful();

        $this->assertTrue(isset($response['thread']));
        $this->assertTrue($response['thread']['id'] == $thread->id);
    }

    public function test_thread_by_entity()
    {
        $discussion = ForumDiscussion::create([
            'account_id' => 1, // a fake account for testing purposes
        ]);
        $thread = ForumThread::create([
            'entity_type' => Morphs::getAlias(ForumDiscussion::class),
            'entity_id' => $discussion->id,
            'subject' => 'Test thread',
            'account_id' => 1,
            'forum_group_id' => 1,
        ]);
        $response = $this->getJson(route('api.discuss.thread-by-entity', ['entityType' => $thread->entity_type, 'entityId' => $thread->entity_id]));
        $response->assertSuccessful();

        $this->assertTrue(isset($response['thread']));
        $this->assertTrue($response['thread']['id'] == $thread->id);
    }

    public function test_resolve()
    {
        $discussion = ForumDiscussion::create([
            'account_id' => 1, // a fake account for testing purposes
        ]);
        $thread = ForumThread::create([
            'entity_type' => Morphs::getAlias(ForumDiscussion::class),
            'entity_id' => $discussion->id,
            'subject' => 'Test thread',
            'account_id' => 1,
            'forum_group_id' => 1,
        ]);
        $response = $this->getJson(route('api.discuss.resolve', ['entityType' => $thread->entity_type, 'entityId' => $thread->entity_id]));
        $response->assertRedirect();
    }

    public function test_resolve_by_post()
    {
        $post = ForumPost::create([
            'account_id' => 1, // a fake account for testing purposes
            'forum_thread_id' => 1,
            'content' => 'Test post',
        ]);
        $response = $this->getJson(route('api.discuss.resolve-by-post', ['postId' => $post->id]));
        $response->assertRedirect();
    }

    public function test_metadata()
    {
        $post = ForumPost::first();
        $response = $this->postJson(route('api.discuss.metadata'), [
            'forum_thread_id' => $post->forum_thread_id,
            'forum_post_id' => [$post->id],
        ]);

        $this->assertTrue(isset($response['likes']));
        $this->assertTrue(isset($response['likes_per_post']));
    }

    public function test_store_like()
    {
        $post = ForumPost::first();
        $response = $this->postJson(route('api.discuss.like'), [
            'forum_post_id' => $post->id,
        ]);
        $response->assertUnauthorized();

        // TODO: Actually store a like
    }

    public function test_store_post()
    {
        $response = $this->postJson(route('api.discuss.store-post'));
        $response->assertUnauthorized();

        // TODO: Actually store a post
    }

    public function test_update_post()
    {
        $post = ForumPost::first();
        $response = $this->putJson(route('api.discuss.update-post', ['postId' => $post->id]));
        $response->assertUnauthorized();

        // TODO: Actually update a post
    }

    public function test_delete_post()
    {
        $post = ForumPost::first();
        $response = $this->deleteJson(route('api.discuss.delete-post', ['postId' => $post->id]));
        $response->assertUnauthorized();

        // TODO: Actually delete a post
    }

    public function test_post_stick()
    {
        $post = ForumPost::first();
        $response = $this->putJson(route('api.discuss.stick', ['postId' => $post->id]));
        $response->assertUnauthorized();

        // TODO: Actually sticky a post
    }

    public function test_feed_posts()
    {
        $response = $this->getJson(route('api.discuss-feed.posts'));
        $response->assertSuccessful();
    }

    public function test_feed_posts_in_group()
    {
        $group = ForumGroup::first();
        $response = $this->getJson(route('api.discuss-feed.posts-in-group', ['groupId' => $group->id]));
        $response->assertSuccessful();
    }
}
