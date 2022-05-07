@if (! empty($contribution->notes))
<div class="well">
  <strong>Author's notes</strong>
  <p>{{ $contribution->notes }}</p>
</div>
@endif