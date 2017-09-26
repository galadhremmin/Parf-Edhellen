@if (! empty($review->notes))
<div class="well">
  <strong>Author's notes</strong>
  <p>{{ $review->notes }}</p>
</div>
@endif