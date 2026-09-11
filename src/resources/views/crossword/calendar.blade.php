@extends('_layouts.default')

@section('title', __('crossword.title.calendar', ['language' => $gameLanguage->getFriendlyName(), 'year' => $year]))
@section('description', __('crossword.description'))
@section('body')

<h1>@lang('crossword.title.calendar', ['language' => $gameLanguage->getFriendlyName(), 'year' => $year])</h1>

{!! Breadcrumbs::render('crossword.calendar', $gameLanguage->language_id, $year) !!}

@auth
<div class="cw-streak-banner">
  @if ($streak > 0)
    <span aria-hidden="true">🔥</span>
    <span><span class="cw-streak-banner__count">{{ $streak }}-week</span> streak — keep it going!</span>
  @else
    <span aria-hidden="true">⭐</span>
    <span>@lang('crossword.calendar.start_streak')</span>
  @endif
</div>
@endauth

<nav class="cw-cal-nav" aria-label="@lang('crossword.calendar.nav')">
  @if ($canShowPrev)
    <a href="{{ route('crossword.calendar', ['languageId' => $gameLanguage->language_id, 'year' => $prevYear]) }}"
       class="cw-cal-nav__arrow" aria-label="@lang('crossword.calendar.prev')">←</a>
  @else
    <span class="cw-cal-nav__arrow cw-cal-nav__arrow--placeholder" aria-hidden="true"></span>
  @endif

  <div class="cw-cal-nav__label">
    <span class="cw-cal-nav__year">{{ $year }}</span>
    @auth
      @if ($availableCount > 0)
        <span class="cw-cal-nav__progress">{{ $completedCount }}/{{ $availableCount }} solved</span>
      @endif
    @endauth
  </div>

  @if ($canShowNext)
    <a href="{{ route('crossword.calendar', ['languageId' => $gameLanguage->language_id, 'year' => $nextYear]) }}"
       class="cw-cal-nav__arrow" aria-label="@lang('crossword.calendar.next')">→</a>
  @else
    <span class="cw-cal-nav__arrow cw-cal-nav__arrow--placeholder" aria-hidden="true"></span>
  @endif
</nav>

{{-- One cell per ISO week. A year has 52 of them, or 53 when the calendar
     drifts far enough; $weeksInYear settles which. --}}
<div class="cw-year-grid" role="list" aria-label="{{ $year }} @lang('crossword.calendar.grid_label')">
  @foreach ($weeks as $week)
    @php
      $range = $week['start']->format('j M') . ' – ' . $week['end']->format('j M');
      $playUrl = $week['has_puzzle']
        ? route('crossword.play', ['languageId' => $gameLanguage->language_id, 'date' => $week['date']])
        : null;
    @endphp

    @if ($week['has_puzzle'] && $week['is_completed'])
      <a href="{{ $playUrl }}"
         class="cw-week cw-week--completed{{ $week['is_current'] ? ' cw-week--current' : '' }}"
         role="listitem"
         title="{{ $range }} — {{ __('crossword.calendar.completed') }}">
        <span class="cw-week__num">{{ $week['number'] }}</span>
        <span class="cw-week__check" aria-hidden="true">✓</span>
      </a>

    @elseif ($week['has_puzzle'])
      <a href="{{ $playUrl }}"
         class="cw-week cw-week--available{{ $week['is_current'] ? ' cw-week--current' : '' }}"
         role="listitem"
         title="{{ $range }} — {{ __('crossword.calendar.has_puzzle') }}">
        <span class="cw-week__num">{{ $week['number'] }}</span>
      </a>

    @elseif ($week['is_current'])
      <div class="cw-week cw-week--current cw-week--empty" role="listitem"
           title="{{ $range }} — @lang('crossword.calendar.not_yet')">
        <span class="cw-week__num">{{ $week['number'] }}</span>
      </div>

    @else
      <div class="cw-week cw-week--empty{{ $week['is_future'] ? ' cw-week--future' : '' }}"
           role="listitem" title="{{ $range }}" aria-hidden="{{ $week['is_future'] ? 'true' : 'false' }}">
        <span class="cw-week__num">{{ $week['number'] }}</span>
      </div>
    @endif
  @endforeach
</div>

<p class="cw-cal-legend ed-ui">
  @lang('crossword.calendar.legend')
  @if ($nextPuzzleDate)
    — @lang('crossword.calendar.next_on', ['date' => $nextPuzzleDate->format('j F')])
  @endif
</p>

@endsection
