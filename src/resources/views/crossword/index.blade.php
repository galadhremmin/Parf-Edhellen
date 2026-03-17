@extends('_layouts.default')

@section('title', __('crossword.title.index'))
@section('description', __('crossword.description'))
@section('body')

<h1>@lang('crossword.title.index')</h1>

{!! Breadcrumbs::render('crossword.index') !!}

<p>@lang('crossword.description')</p>

@if ($languages->isEmpty())
  <p>No crosswords are available yet. Check back later.</p>
@else
  <div class="link-blocks">
    @foreach ($languages as $gameLanguage)
    <blockquote>
      <a class="block-link" href="{{ route('crossword.calendar', ['languageId' => $gameLanguage->language_id]) }}">
        <h3>
          {{ $gameLanguage->getFriendlyName() }}
          @if (! empty($gameLanguage->language->tengwar))
          <span class="tengwar">{{ $gameLanguage->language->tengwar }}</span>
          @endif
        </h3>
        @if ($gameLanguage->description)
        <p>{{ $gameLanguage->description }}</p>
        @elseif ($gameLanguage->language?->description)
        <p>{{ $gameLanguage->language->description }}</p>
        @endif
      </a>
    </blockquote>
    @endforeach
  </div>
@endif

@endsection
