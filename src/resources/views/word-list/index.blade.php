@extends('_layouts.default')

@section('title', __('word-list.title'))
@section('description', __('word-list.description'))
@section('body')

<h1>@lang('word-list.title')</h1>

{!! Breadcrumbs::render('word-list.index') !!}

<p>@lang('word-list.description')</p>

@if (count($wordLists) < 1)
  <div class="alert alert-info">
    <strong><span class="TextIcon TextIcon--info-sign" aria-hidden="true"></span> @lang('word-list.empty')</strong>
    <p class="mb-0">@lang('word-list.empty-call-to-action')</p>
  </div>
@else
  <div class="link-blocks">
    @foreach ($wordLists as $wordList)
    <blockquote>
      <a class="block-link" href="{{ $wordList['url'] }}">
        <h3>{{ $wordList['name'] }}</h3>
        <p>
          {{ trans_choice('word-list.number-of-entries', $wordList['number_of_entries'] ?? 0, ['count' => $wordList['number_of_entries'] ?? 0]) }}
          @if ($wordList['is_public'])
            &middot; @lang('word-list.public')
          @endif
        </p>
        @if (! empty($wordList['description']))
        <p>{{ $wordList['description'] }}</p>
        @endif
      </a>
    </blockquote>
    @endforeach
  </div>
@endif

@endsection
