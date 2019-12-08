@inject('link', 'App\Helpers\LinkHelper')
@component('mail::message')
# _[{{ $post->account->nickname }}]({{ $link->author($post->account_id) }})_ has commented on _{{ $post->forum_thread->subject }}_

This is a notification that the thread you are subscribed to has changed.
If you do not believe you have subscribed to this thread, {{ config('app.name') }}
has probably notified you because you have posted to it. 

@component('mail::button', ['url' => $link->resolveThreadByPost($post->id)])
Show {{ $post->account->nickname }}'s post
@endcomponent

You can unsubscribe from this thread by [clicking the link]({{ $link->mailCancellation($cancellationToken) }}). Alternatively, you can log in 
to [{{ config('app.name') }}]({{ config('app.url') }}) and configure what you would like to
be subscribed to on your dashboard.

Thanks,<br>
{{ config('app.name') }}

@endcomponent
