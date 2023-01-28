@inject('linker', 'App\Helpers\LinkHelper')

<a class="r {{ $thread->is_sticky ? 'sticky' : '' }}" href="{{ $linker->forumThread($group->id, $group->name, $thread->id, $thread->normalized_subject) }}">
  <div class="c">
    @include('discuss._avatar', ['account' => $thread->account])
  </div>
  <div class="c p2">
      @if ($thread->is_sticky)
      <span class="TextIcon TextIcon--pushpin" title="This post has been pinned to the top by an administrator."></span>
      @endif
      <span class="subject">{{ $thread->subject }}</span>
    <div class="pi">
      {{ $thread->account ? $thread->account->nickname : 'nobody' }} &bull; 
      @date($thread->updated_at ?: $thread->created_at)
    </div>
  </div>
  <div class="c text-end">
    <span class="TextIcon TextIcon--comment" title="Number of comments"></span> {{ $thread->number_of_posts }}
    <span class="TextIcon TextIcon--thumbs-up" title="Number of likes"></span> {{ $thread->number_of_likes }}
  </div>
</a>
