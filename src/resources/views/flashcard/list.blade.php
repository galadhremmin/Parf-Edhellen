@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Flashcard results for '.$flashcard->language->name)
@section('body')
  <h1>Results for {{ $flashcard->language->name }}</h1>
  
  {!! Breadcrumbs::render('flashcard.list', $flashcard) !!}
  @if (! empty($flashcard->description))
  <p>{{ $flashcard->description }}</p>
  <p>You have answered {{ count($results) }} flashcards for this language.</p>
  @endif

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Date</th>
        <th>Word</th>
        <th>Expected</th>
        <th>Answer</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($results as $r)
      <tr class="{{ $r->correct ? '' : 'danger' }}">
        <td class="date">{{ $r->created_at }}</td>
        <td>
          <a href="{{ $link->gloss($r->gloss_id) }}">
            {{ $r->gloss->word->word }}
          </a>
        </td>
        <td>{{ $r->expected }}</td>
        <td>
          <span class="{{ $r->correct ? 'text-success' : 'text-danger' }}">
            <span class="glyphicon {{ $r->correct ? 'glyphicon-ok' : 'glyphicon-remove' }}"></span>
            {{ $r->actual }}
          </span>
        </td>
      </tr>
      </a>
      @endforeach
    </tbody>
  </table>

@endsection
