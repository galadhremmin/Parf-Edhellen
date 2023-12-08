@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Glossary by '.$author->nickname)

@section('body')
  <h1>Glossary by {{ $author->nickname }}</h1>
  @if (count($glossary))
  <p>These are the {{ $glossary->count() }} latest contributions by <a href="{{ $link->author($author->id, $author->nickname) }}">{{ $author->nickname }}</a>.</p>

  <table class="table table-hover table-striped">
    <thead>
      <tr>
        <th class="hidden-xs">Created</th>
        <th>Language</th>
        <th>Word / sense</th>
        <th>Gloss</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($glossary as $gloss)
      <tr>
        <td class="hidden-xs date">@date($gloss->created_at)</td>
        <td>{{ $gloss->language->name }}</td>
        <td>
          @if ($gloss->is_uncertain || ! $gloss->is_canon)
          <span class="TextIcon TextIcon--asterisk"></span>
          @endif
          <a href="{{ $link->gloss($gloss->id) }}">
            {{ $gloss->word }} / {{ $gloss->sense }}
          </a>
        </td>
        <td>{{ $gloss->all_translations }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <em>There are no recorded contributions by this account.</em>
  @endif
@endsection
