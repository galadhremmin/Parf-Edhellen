@inject('link', 'App\Helpers\LinkHelper')
@component('mail::message')
# _[{{ $post->account->nickname }}]({{ $link->author($post->account_id) }})_ has commented on your profile

This is a notification that someone has attempted to reach out to you on your profile page.

@component('mail::button', ['url' => $link->resolveThreadByPost($post->id)])
Show {{ $post->account->nickname }}'s post
@endcomponent

You can unsubscribe from this thread by [clicking the link]({{ $link->mailCancellation($cancellationToken) }}). Alternatively, you can log in 
to [{{ config('app.name') }}]({{ config('app.url') }}) and configure what you would like to
be subscribed to on your dashboard.

Thanks,<br>
{{ config('app.name') }}

@endcomponent
