@inject('link', 'App\Helpers\LinkHelper')

@extends('_layouts.default')

@section('title', 'Versions of ' . ucfirst($word))

@section('body')
<div class="ed-remove-when-navigating">
  <h2>Versions of <em>{{ ucfirst($word) }}</em></h2>
  <p>
    All previous versions including the latest version of this word are available below.
    Comments are inserted between the versions, in descending, chronological order. You can comment on the latest version.
  </p>
  @foreach ($versions as $v)
  <a name="ed-gloss-version-{{ $v->id }}-container"></a>
  <div class="card {{ $v->_is_latest ? 'shadow' : 'bg-light text-muted' }} position-relative mb-4">
    @if ($v->_is_latest)
    <span class="position-absolute top-0 start-100 translate-middle p-2 bg-danger border border-light rounded-circle" title="Latest version"><span class="visually-hidden">Latest version</span></span>
    @endif
    <div class="card-body">
      <div class="text-end">
        @date($v->created_at) · <em>
          @if ($v->_is_latest) 
            Latest
          @else 
            Deprecated
          @endif
          version</em>
      </div>
      @include('book._gloss', [ 
        'gloss' => $v, 
        'language' => $v->language,
        'disable_tools' => true
      ])
      @if (! empty($v->_recorded_changes))
      <strong>Changes recorded:</strong> {{ implode(', ', $v->_recorded_changes) }}
      @endif
      <div class="text-end">
        <a class="btn btn-secondary" href="{{ $link->contributeGloss(0, $v->id) }}">
          {{ $v->_is_latest ? 'Propose changes' : 'Restore' }}
        </a>
        @if ($user !== null && $user->isAdministrator())
        <a class="btn btn-secondary" data-bs-toggle="collapse" href="#ed-gloss-version-{{ $v->id }}-container" role="button" data-bs-target="#ed-gloss-version-{{ $v->id }}">
          View JSON
        </a>
        <textarea class="collapse form-control" rows="10" id="ed-gloss-version-{{ $v->id }}">{{ json_encode($v, JSON_PRETTY_PRINT) }}</textarea>
        @endif
      </div>
    </div>
  </div>

  @include('discuss._standalone', [
    'entity_id'   => $v->id,
    'entity_type' => 'lex_entry_ver',
    'enabled'     => !! $v->_is_latest
  ])

  @endforeach
</div>
@endsection
