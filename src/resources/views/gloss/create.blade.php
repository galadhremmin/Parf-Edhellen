@extends('_layouts.default')

@section('title', 'Add gloss - Administration')
@section('body')

<h1>Add gloss</h1>
{!! Breadcrumbs::render('gloss.create') !!}

<div id="ed-gloss-form" data-inject-module="form-gloss"></div>

@endsection
