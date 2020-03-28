@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases - Administration')
@section('body')

<h1>Phrases</h1>
{!! Breadcrumbs::render('sentence.index') !!}

<p>Click on a phrase beneath to edit it.</p>

<a class="btn btn-primary" href="{{ $link->contributeSentence() }}">Add phrase</a>

@if (count($sentences) < 1)
<em>No known phrases.</em> 
@else
  @foreach ($sentences as $languageName => $sentencesForLanguage)
    <h2>{{ $languageName }}</h2>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Author</th>
          <th>Flag</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($sentencesForLanguage as $sentence)
        <tr>
          <td>{{ $sentence->id }}</td>
          <td><a href="{{ $link->contributeSentence($sentence->id) }}">{{ $sentence->name }}</a></td>
          <td>{{ $sentence->account_name }}</td>
          <td>{{ $sentence->is_neologism ? 'Neologism' : 'Attested' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endforeach
@endif

@endsection
