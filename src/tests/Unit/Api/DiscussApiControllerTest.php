<?php

namespace Tests\Unit\Api;

use Illuminate\Support\Str;
use Tests\TestCase;
use App\Http\Controllers\Api\v2\DiscussApiController;
use App\Models\{
    ForumGroup,
    ForumPost,
    ForumThread
};

class DiscussApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        /**
         * This disables the exception handling to display the stacktrace on the console
         * the same way as it shown on the browser
         */
        parent::setUp();
        // $this->withoutExceptionHandling();
    }

    public function testGroups()
    {
        $response = $this->getJson(route('api.discuss.groups'));
        $response->assertSuccessful();
    }

    public function testGroup()
    {
        $response = $this->getJson(route('api.discuss.group', ['groupId' => 0]));
        $response->assertNotFound();

        $group = ForumGroup::first();
        $response = $this->getJson(route('api.discuss.group', ['groupId' => $group->id]));
        $response->assertSuccessful();

        $this->assertTrue(isset($response['group']));
        $this->assertTrue($response['group']['id'] == $group->id);
    }

    public function testThreads()
    {
        $response = $this->getJson(route('api.discuss.threads'));
        $response->assertSuccessful();
    }

    public function testThread()
    {
        $response = $this->getJson(route('api.discuss.thread', ['threadId' => 0]));
        $response->assertNotFound();

        $thread = ForumThread::first();
        $response = $this->getJson(route('api.discuss.thread', ['threadId' => $thread->id]));
        $response->assertSuccessful();

        $this->assertTrue(isset($response['thread']));
        $this->assertTrue($response['thread']['id'] == $thread->id);
    }

    public function testThreadByEntity()
    {
        $thread = ForumThread::first();
        $response = $this->getJson(route('api.discuss.thread-by-entity', ['entityType' => $thread->entity_type, 'entityId' => $thread->entity_id]));
        $response->assertSuccessful();

        $this->assertTrue(isset($response['thread']));
        $this->assertTrue($response['thread']['id'] == $thread->id);
    }

    public function testResolve()
    {
        $thread = ForumThread::first();
        $response = $this->getJson(route('api.discuss.resolve', ['entityType' => $thread->entity_type, 'entityId' => $thread->entity_id]));
        $response->assertRedirect();
    }

    public function testResolveByPost()
    {
        $post = ForumPost::first();
        $response = $this->getJson(route('api.discuss.resolve-by-post', ['postId' => $post->id]));
        $response->assertRedirect();
    }

    public function testMetadata()
    {
        $post = ForumPost::first();
        $response = $this->postJson(route('api.discuss.metadata'), [
            'forum_thread_id' => $post->forum_thread_id,
            'forum_post_id'   => [$post->id]
        ]);

        $this->assertTrue(isset($response['likes']));
        $this->assertTrue(isset($response['likes_per_post']));
    }

    public function testStoreLike()
    {
        $post = ForumPost::first();
        $response = $this->postJson(route('api.discuss.like'), [
            'forum_post_id'   => $post->id
        ]);
        $response->assertUnauthorized();

        // TODO: Actually store a like
    }

    public function testStorePost()
    {
        $response = $this->postJson(route('api.discuss.store-post'));
        $response->assertUnauthorized();

        // TODO: Actually store a post
    }

    public function testUpdatePost()
    {
        $post = ForumPost::first();
        $response = $this->putJson(route('api.discuss.update-post', ['postId' => $post->id]));
        $response->assertUnauthorized();

        // TODO: Actually update a post
    }

    public function testDeletePost()
    {
        $post = ForumPost::first();
        $response = $this->deleteJson(route('api.discuss.delete-post', ['postId' => $post->id]));
        $response->assertUnauthorized();

        // TODO: Actually delete a post
    }

    public function testPostStick()
    {
        $post = ForumPost::first();
        $response = $this->putJson(route('api.discuss.stick', ['postId' => $post->id]));
        $response->assertUnauthorized();

        // TODO: Actually sticky a post
    }

    public function testFeedPosts()
    {
        $response = $this->getJson(route('api.discuss-feed.posts'));
        $response->assertSuccessful();
    }

    public function testFeedPostsInGroup()
    {
        $group = ForumGroup::first();
        $response = $this->getJson(route('api.discuss-feed.posts-in-group', ['groupId' => $group->id]));
        $response->assertSuccessful();
    }
}
