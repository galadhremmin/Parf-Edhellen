@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Glossary for '.$language->name)
@section('body')
  <h1>Glossary for {{ $language->name }}</h1>

  {!! Breadcrumbs::render('gloss.list', $language) !!}

  @include('_shared._errors', [ 'errors' => $errors ])

  <form method="get" class="card shadow-sm mb-3">
    <div class="card-body row g-2 align-items-end">
      <div class="col-sm-6 col-lg-3">
        <label for="ed-filter-word" class="form-label">Word</label>
        <input type="search" class="form-control" id="ed-filter-word" name="word" value="{{ $filters['word'] ?? '' }}">
      </div>
      <div class="col-sm-6 col-lg-3">
        <label for="ed-filter-gloss" class="form-label">Gloss</label>
        <input type="search" class="form-control" id="ed-filter-gloss" name="gloss" value="{{ $filters['gloss'] ?? '' }}">
      </div>
      <div class="col-sm-6 col-lg-2">
        <label for="ed-filter-sense" class="form-label">Sense</label>
        <input type="search" class="form-control" id="ed-filter-sense" name="sense" value="{{ $filters['sense'] ?? '' }}">
      </div>
      <div class="col-sm-6 col-lg-2">
        <label for="ed-filter-speech" class="form-label">Type of speech</label>
        <select class="form-select" id="ed-filter-speech" name="speech_id">
          <option value="">Any</option>
          @foreach ($speeches as $speech)
          <option value="{{ $speech->id }}" @selected(($filters['speech_id'] ?? null) == $speech->id)>{{ $speech->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-6 col-lg-2">
        <label for="ed-filter-missing" class="form-label">Show only</label>
        <select class="form-select" id="ed-filter-missing" name="missing">
          <option value="">All entries</option>
          <option value="source" @selected(($filters['missing'] ?? null) === 'source')>Missing source</option>
          <option value="sense" @selected(($filters['missing'] ?? null) === 'sense')>Missing sense</option>
        </select>
      </div>
      <div class="col-12 d-flex align-items-center gap-2">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('gloss.list', ['id' => $language->id]) }}" class="btn btn-secondary">Clear</a>
        <span class="ms-auto text-body-secondary">{{ $glosses->total() }} {{ \Illuminate\Support\Str::plural('entry', $glosses->total()) }}</span>
      </div>
    </div>
  </form>

  @if (session('updated_sense_id'))
  <div class="alert alert-success">Sense updated for #{{ session('updated_sense_id') }}.</div>
  @endif

  <ul class="list-group">
    @forelse ($glosses as $t)
    <li class="list-group-item" id="lexical-entry-{{ $t->id }}">
      <p>
        <a href="{{ $link->contributeGloss($t->id) }}"{!! $t->is_rejected ? 'style="text-decoration:line-through"' : '' !!}>
          <strong>{{ $t->word->word }}</strong>
        </a>
        @if ($t->speech)
        <em>{{ $t->speech->name }}</em>
        @endif
        {{ $t->glosses->implode('translation', ', ') }}
        @if (! empty($t->source))
        [<span class="text-info">{{ $t->source }}</span>]
        @else
        [<strong class="text-danger">SOURCE MISSING</strong>]
        @endif
        by
        <a href="{{ $link->author($t->account_id, $t->account->nickname) }}">{{ $t->account->nickname }}</a>
        in
        @if ($t->sense)
        <strong>{{ $t->sense->word->word }}</strong>
        @else
        <strong class="text-danger">SENSE MISSING</strong>
        @endif
        |
        #<a href="{{ $link->lexicalEntry($t->id) }}">{{ $t->id }}</a>
      </p>
      @foreach ($t->keywords as $k)
      <span class="badge bg-secondary">{{ $k->keyword }}</span>
      @endforeach
      <details class="mt-2">
        <summary class="small">Change sense</summary>
        <form method="post" action="{{ route('gloss.update-sense', ['id' => $t->id]) }}" class="input-group input-group-sm mt-2" style="max-width: 24rem">
          <input type="text" class="form-control" name="sense" required
                 value="{{ $t->sense?->word?->word }}" aria-label="Sense for {{ $t->word->word }}">
          <button type="submit" class="btn btn-primary">Save</button>
          {{ csrf_field() }}
          {{ method_field('PUT') }}
        </form>
      </details>
    </li>
    @empty
    <li class="list-group-item"><em>No entries match these filters.</em></li>
    @endforelse
  </ul>

  {{ $glosses->links() }}

@endsection
