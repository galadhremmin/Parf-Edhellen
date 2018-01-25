@inject('link', 'App\Helpers\LinkHelper')
@component('mail::message')
# Your contribution &ldquo;{{ $contribution->word }}&rdquo; was rejected

This is a notification that your contribution has been reviewed and rejected by 
our team of reviewers.
@if (! empty($contribution->justification))

_{{ $contribution->justification }}_

@endif

A contribution is usually rejected when the review team believes that it lacks
information, or when they believe that the author should further elucidate on their conclusions.
Listen to their feedback, and you might be able to rework the contribution so it would meet their standards. 

You can view the rejected contribution by tapping the button below.

@component('mail::button', ['url' => $link->contribution($contribution->id)])
Show your contribution
@endcomponent

@include('emails._footer-without-token')

@endcomponent
