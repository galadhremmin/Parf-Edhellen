@inject('link', 'App\Helpers\LinkHelper')
@component('mail::message')
# Your contribution &ldquo;{{ $contribution->word }}&rdquo; was approved!

This is a notification that your contribution has been reviewed and approved by 
our team of reviewers. You can view the approved contribution by tapping the button
below.

@component('mail::button', ['url' => $link->contribution($contribution->id)])
Show approved contribution
@endcomponent

@include('emails._footer-without-token')

@endcomponent
