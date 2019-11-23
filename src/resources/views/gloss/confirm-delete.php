@extends('_layouts.default')

@section('title', 'Delete gloss - Administration')
@section('body')

<h1>Delete gloss &ldquo;{{ $gloss->word->word }}&rdquo;</h1>
{!! Breadcrumbs::render('gloss.confirm-delete', $gloss) !!}

@endsection
