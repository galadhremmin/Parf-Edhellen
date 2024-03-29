@inject('link', 'App\Helpers\LinkHelper')
@component('mail::message')
You have requested that we link your {{ $providerList }} account(s). Once you have linked your accounts, they'll all be connected with the same account. Please press the button below to link your accounts.

@component('mail::button', ['url' => route('account.confirm-merge', [ 'requestId' => $requestId, 'token' => $token ])])
Link accounts
@endcomponent

If you don't recall initiating this request, your account might be compromised. In that case, make sure to change your social media passwords and let us know about the breach.

Thanks,<br>
{{ config('app.name') }}

@endcomponent
