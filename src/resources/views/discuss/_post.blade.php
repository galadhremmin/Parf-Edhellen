<div class="forum-post" id="forum-post-{{ $post->id }}">
    <div class="post-profile-picture">
        <a href="/author/{{ $post->account_id }}" title="View {{$post->account->nickname}}'s profile">
            <img src="{{ $post->account->has_avatar ? "/storage/avatars/".$post->account_id.".png" : "/img/anonymous-profile-picture.png"}}" />
        </a>
    </div>
    <div class="post-content">
        <div class="post-header">
            <a href="/author/{{ $post->account_id }} title="View {{ $post->account->nickname }}'s profile" class="nickname">
                {{ $post->account->nickname }}
            </a>
            <span class="post-no">#{{ $post->id }}</span>
        </div>
        <div className="post-body">
            @if (! $post->is_deleted)
                {!! $post->content !!}
            @else
                <em>{{ $post->account->nickname }} has redacted their comment.</em>
            @endif
        </div>
        <div className="post-tools">
            <span className="date">{{ $post->created_at }}</span>
        </div> 
    </div>
</div>
