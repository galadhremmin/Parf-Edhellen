@extends('_layouts.default')

@section('title', __('crossword.title.calendar', ['language' => $gameLanguage->getFriendlyName(), 'month' => $startOfMonth->format('F'), 'year' => $year]))
@section('description', __('crossword.description'))
@section('body')

<h1>@lang('crossword.title.calendar', ['language' => $gameLanguage->getFriendlyName(), 'month' => $startOfMonth->format('F'), 'year' => $year])</h1>

{!! Breadcrumbs::render('crossword.calendar', $gameLanguage->language_id, $year, $month) !!}

@auth
<div class="cw-streak-banner">
  @if ($streak > 0)
    <span>🔥</span>
    <span><span class="cw-streak-banner__count">{{ $streak }}-day</span> streak — keep it going!</span>
  @else
    <span>⭐</span>
    <span>Complete today's puzzle to start your streak!</span>
  @endif
</div>
@endauth

<nav class="cw-cal-nav" aria-label="Calendar month navigation">
  <a href="{{ route('crossword.calendar', ['languageId' => $gameLanguage->language_id, 'year' => $prevYear, 'month' => $prevMonth]) }}"
     class="cw-cal-nav__arrow" aria-label="@lang('crossword.calendar.prev')">←</a>

  <div class="cw-cal-nav__label">
    <span class="cw-cal-nav__month">{{ $startOfMonth->format('F Y') }}</span>
    @auth
      @if ($puzzles->count() > 0)
        <span class="cw-cal-nav__progress">{{ $monthCompletedCount }}/{{ $puzzles->count() }} solved</span>
      @endif
    @endauth
  </div>

  @if ($canShowNext)
    <a href="{{ route('crossword.calendar', ['languageId' => $gameLanguage->language_id, 'year' => $nextYear, 'month' => $nextMonth]) }}"
       class="cw-cal-nav__arrow" aria-label="@lang('crossword.calendar.next')">→</a>
  @else
    <span class="cw-cal-nav__arrow cw-cal-nav__arrow--placeholder" aria-hidden="true"></span>
  @endif
</nav>

<div class="cw-cal-grid" role="grid" aria-label="{{ $startOfMonth->format('F Y') }} crossword calendar">
  {{-- Day-of-week headers --}}
  <div class="cw-day-header" role="columnheader">Mon</div>
  <div class="cw-day-header" role="columnheader">Tue</div>
  <div class="cw-day-header" role="columnheader">Wed</div>
  <div class="cw-day-header" role="columnheader">Thu</div>
  <div class="cw-day-header" role="columnheader">Fri</div>
  <div class="cw-day-header" role="columnheader">Sat</div>
  <div class="cw-day-header" role="columnheader">Sun</div>

  @php
    $todayStr    = $today->format('Y-m-d');
    $day         = $startOfMonth->copy();
    $daysFromMon = $day->dayOfWeekIso - 1;
    if ($daysFromMon > 0) {
        $day->subDays($daysFromMon);
    }
    $weeks = [];
    for ($w = 0; $w < 6; $w++) {
        $row = [];
        for ($d = 0; $d < 7; $d++) {
            $row[] = $day->copy();
            $day->addDay();
        }
        $weeks[] = $row;
    }
  @endphp

  @foreach ($weeks as $week)
    @foreach ($week as $cellDate)
      @php
        $dateStr     = $cellDate->format('Y-m-d');
        $isThisMonth = $cellDate->month === (int) $month;
        $hasPuzzle   = $puzzles->has($dateStr);
        $isCompleted = in_array($dateStr, $completedDates, true);
        $isToday     = $dateStr === $todayStr;
        $isTomorrow  = $dateStr === $tomorrowStr && $hasTomorrowPuzzle;
      @endphp

      @if (!$isThisMonth)
        {{-- Off-month: near-invisible filler --}}
        <div class="cw-day cw-day--off-month" role="gridcell" aria-hidden="true">
          <span class="cw-day__num">{{ $cellDate->day }}</span>
        </div>

      @elseif ($isToday && $isCompleted)
        {{-- Today + solved --}}
        <a href="{{ route('crossword.play', ['languageId' => $gameLanguage->language_id, 'date' => $dateStr]) }}"
           class="cw-day cw-day--today cw-day--completed"
           role="gridcell"
           title="{{ __('crossword.calendar.completed') }}">
          <span class="cw-day__check" aria-hidden="true">✓</span>
          <span class="cw-day__num">{{ $cellDate->day }}</span>
          <span class="cw-day__today-badge">Today</span>
        </a>

      @elseif ($isToday && $hasPuzzle)
        {{-- Today — unsolved, primary CTA --}}
        <a href="{{ route('crossword.play', ['languageId' => $gameLanguage->language_id, 'date' => $dateStr]) }}"
           class="cw-day cw-day--today"
           role="gridcell"
           title="{{ __('crossword.calendar.has_puzzle') }}">
          <span class="cw-day__num">{{ $cellDate->day }}</span>
          <span class="cw-day__cta">Play →</span>
        </a>

      @elseif ($isToday)
        {{-- Today — no puzzle generated yet --}}
        <div class="cw-day cw-day--today cw-day--empty" role="gridcell" aria-label="{{ $cellDate->day }}, today, no puzzle yet">
          <span class="cw-day__num">{{ $cellDate->day }}</span>
          <span class="cw-day__today-badge">Today</span>
        </div>

      @elseif ($isCompleted)
        {{-- Past puzzle, solved --}}
        <a href="{{ route('crossword.play', ['languageId' => $gameLanguage->language_id, 'date' => $dateStr]) }}"
           class="cw-day cw-day--completed"
           role="gridcell"
           title="{{ __('crossword.calendar.completed') }}">
          <span class="cw-day__check" aria-hidden="true">✓</span>
          <span class="cw-day__num">{{ $cellDate->day }}</span>
        </a>

      @elseif ($hasPuzzle)
        {{-- Past puzzle, not yet solved --}}
        <a href="{{ route('crossword.play', ['languageId' => $gameLanguage->language_id, 'date' => $dateStr]) }}"
           class="cw-day cw-day--available"
           role="gridcell"
           title="{{ __('crossword.calendar.has_puzzle') }}">
          <span class="cw-day__num">{{ $cellDate->day }}</span>
        </a>

      @elseif ($isTomorrow)
        {{-- Tomorrow's puzzle exists in DB but is not yet playable --}}
        <div class="cw-day cw-day--tomorrow" role="gridcell" aria-label="Puzzle coming tomorrow">
          <span class="cw-day__num">{{ $cellDate->day }}</span>
          <span class="cw-day__lock" aria-hidden="true">🔒</span>
          <span class="cw-day__tomorrow-label">Tomorrow</span>
        </div>

      @else
        {{-- No puzzle this month, or future date without a puzzle --}}
        <div class="cw-day cw-day--empty" role="gridcell" aria-label="{{ $cellDate->day }}, no puzzle">
          <span class="cw-day__num">{{ $cellDate->day }}</span>
        </div>

      @endif
    @endforeach
  @endforeach
</div>

@endsection
