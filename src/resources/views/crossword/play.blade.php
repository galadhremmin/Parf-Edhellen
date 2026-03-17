@extends('_layouts.default')

@section('title', __('crossword.title.play', ['language' => $gameLanguage->getFriendlyName(), 'date' => \Carbon\Carbon::parse($date)->format('j F Y')]))
@section('description', __('crossword.description'))
@section('body')

<h1>@lang('crossword.title.play', ['language' => $gameLanguage->getFriendlyName(), 'date' => \Carbon\Carbon::parse($date)->format('j F Y')])</h1>

{!! Breadcrumbs::render('crossword.play', $gameLanguage->language_id, $date) !!}

<div data-inject-module="crossword"
     data-inject-prop-language-id="{{ $gameLanguage->language_id }}"
     data-inject-prop-date="{{ $date }}"
     data-inject-prop-initial-state="@json($initialState)"></div>

@if (Auth::check() && Auth::user()->isAdministrator())
<details class="mt-4">
    <summary class="text-muted" style="cursor:pointer;font-size:0.85rem">
        🔑 Admin — puzzle answers (ID {{ $puzzle->id }})
    </summary>
    <pre class="mt-2 p-3 border rounded bg-body-tertiary" style="font-size:0.8rem;max-height:60vh;overflow:auto">{{ json_encode($puzzle->clues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</details>
@endif

@endsection
