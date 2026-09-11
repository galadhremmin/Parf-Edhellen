{{-- The colophon: the note at the back of a book saying who set it.

     This used to be a small card in a sidebar, in the third person, which is
     what made it read as a footer notice rather than an ask. A book's own
     closing note is the honest place for it.

     Optional: $searchesPerDay (int). Omitted or zero, the figure is left out
     entirely -- an empty brag is worse than none. --}}
<section class="ed-colophon">
  <div class="ed-rule ed-colophon__rule">&#10022;</div>

  <div class="ed-colophon__inner">
    <div class="ed-colophon__note">
      <p class="ed-label">{{ __('donations.colophon.label') }}</p>
      <h2 class="ed-colophon__heading">{{ __('donations.colophon.heading', ['periodSinceInception' => $periodSinceInception]) }}</h2>
      <p class="ed-colophon__lead">{{ __('donations.colophon.lead') }}</p>
      <p class="ed-colophon__body">{{ __('donations.colophon.body') }}</p>
      <p class="ed-colophon__signature">
        &mdash; {{ __('donations.colophon.signature') }},
        <a href="https://twitter.com/parmaeldo" target="_blank" rel="noopener noreferrer">&#64;parmaeldo</a>
      </p>
    </div>

    <div class="ed-colophon__plate">
      <span class="TextIcon TextIcon--heart ed-colophon__mark" aria-hidden="true"></span>
      @if (! empty($searchesPerDay))
      <p class="ed-colophon__figure">
        {!! __('donations.colophon.figure', ['searches' => '<strong>' . number_format($searchesPerDay) . '</strong>']) !!}
      </p>
      @endif
      <a href="https://www.buymeacoffee.com/elfdict.com" target="_blank" rel="noopener noreferrer" class="btn btn-primary ed-colophon__cta">
        {{ __('donations.buymeacoffee.cta') }}
      </a>
      <p class="ed-label ed-colophon__alternatives">
        {{ __('donations.colophon.otherwise') }}
        <a href="{{ route('contribution.index') }}">{{ __('donations.colophon.contribute') }}</a>
        &#183;
        <a href="https://github.com/galadhremmin/Parf-Edhellen" target="_blank" rel="noopener noreferrer">{{ __('donations.colophon.source') }}</a>
      </p>
    </div>
  </div>

  <div class="ed-rule ed-colophon__rule">&#10022;</div>
</section>
