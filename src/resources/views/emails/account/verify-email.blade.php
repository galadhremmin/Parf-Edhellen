@component('mail::message')
Welcome to {{ config('app.name') }}, {{ $nickname }}! Confirm that this is your e-mail address and you're in.

Enter this code on the page that asked for it:

@component('mail::panel')
# {{ $code }}
@endcomponent

Or, if you're reading this in the same browser, press the button:

@component('mail::button', ['url' => $link])
Confirm my e-mail address
@endcomponent

The code works for {{ $lifetime }} minutes. If you didn't sign up for {{ config('app.name') }}, ignore this e-mail.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
