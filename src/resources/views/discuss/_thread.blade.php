@inject('linker', 'App\Helpers\LinkHelper')

<div class="r {{ $thread->is_sticky ? 'sticky' : '' }}">
    <div class="c">
    @include('discuss._avatar', ['account' => $thread->account])
    </div>
    <div class="c p2">
    <a href="{{ $linker->forumThread($group->id, $group->name, $thread->id, $thread->normalized_subject) }}">
        @if ($thread->is_sticky)
        <span class="glyphicon glyphicon-pushpin" title="This post has been pinned to the top by an administrator."></span>
        @endif
        {{ $thread->subject }}
    </a>
    <div class="pi">
        {{ $thread->account ? $thread->account->nickname : 'nobody' }} on
        <span class="date">{{ $thread->updated_at ?: $thread->created_at }}</span>
    </div>
    </div>
    <div class="c text-right">
    {{ $thread->number_of_posts }} <span class="glyphicon glyphicon-comment"></span>
    {{ $thread->number_of_likes }} <span class="glyphicon glyphicon-thumbs-up"></span>
    </div>
</div>
