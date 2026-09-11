@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', __('practice.title'))
@section('description', __('practice.description'))
@section('body')
<h1>{{ __('practice.title') }}</h1>

{!! Breadcrumbs::render('practice') !!}

<div class="link-blocks">
  @foreach ($activities as $activity)
  <blockquote>
    <a class="block-link" href="{{ $activity->route }}">
      <h3>
        {{ $activity->title }}
      </h3>
      <p>
        {{ $activity->description }}
      </p>
    </a>
  </blockquote>
  @endforeach
</div>

@endsection
