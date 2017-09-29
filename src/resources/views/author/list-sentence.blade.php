@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases by '.$author->nickname)

@section('body')
  <h1>Phrases by {{ $author->nickname }}</h1>
  @if (count($sentences))
  <p>These are the {{ $sentences->count() }} latest phrases by <a href="{{ $link->author($author->id, $author->nickname) }}">{{ $author->nickname }}</a>.</p>

  <table class="table table-hover table-striped">
    <thead>
      <tr>
        <th>Created</th>
        <th>Language</th>
        <th>Phrase</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($sentences as $sentence)
      <tr>
        <td>{{ $sentence->created_at->format('Y-m-d H:i') }}</td>
        <td>{{ $sentence->language->name }}</td>
        <td>
            <a href="{{ $link->sentence($sentence->language_id, $sentence->language->name, $sentence->id, $sentence->name) }}">
                {{ $sentence->name }}
            </a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <em>There are no recorded contributions by this account.</em>
  @endif
@endsection
