@if ($contribution->dependencies->count() > 0)
<div class="alert bg-info">
  <strong>There are contributions that depend on this contribution:</strong>
  <ul>
      @foreach ($contribution->dependencies as $dependency)
      <li>
      <a href="{{ route('contribution.show', ['contribution' => $dependency->id]) }}">
          {{ $dependency->word }}
      </a> by
      <a href="{{ $link->author($dependency->account_id, $dependency->account->nickname) }}">
          {{ $dependency->account->nickname }}
      </a>
      </li>
      @endforeach
  </ul>
  <p class="mb-0">
      Dependent contributions are reviewed independently but they can only be approved
      once their dependencies are approved.
  </p>
</div>
@elseif ($contribution->dependent_on !== null)
<div class="alert bg-info">
  <strong>
    This contribution is dependent on
    <a href="{{ route('contribution.show', ['contribution' => $contribution->dependent_on->id]) }}">
          {{ $contribution->dependent_on->word }}
      </a> by
      <a href="{{ $link->author($contribution->dependent_on->account_id, $contribution->dependent_on->account->nickname) }}">
          {{ $contribution->dependent_on->account->nickname }}
      </a>
  </strong>. This contribution can only be approved once its dependencies are approved.
</div>
@endif
