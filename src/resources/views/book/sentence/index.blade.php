@if (count($entities['sections']) < 1 )
<h3>Forsooth! I can't find what you're looking for!</h3>
<p>The word <em>{{ $word }}</em> hasn't been recorded for any of the languages.</p>
@else
@foreach ($entities['sections'] as $section)
<article className="ed-glossary__language">
  <header>
    <h2>{{ $section['language']['name'] }}</h2>
  </header>
  <section className="link-blocks">
    @foreach ($section['entities'] as $entity)
    <blockquote>
      <a class="block-link" href="{{ $entity['link_href'] }}">
        <h3>{{ $entity['name'] }}</h3>
        <p>
          {{ $entity['description'] }}
        </p>
      </a>
      <footer>
        {{ $entity['source'] }}
        by
        <a href="/author/{{ $entity['account_id'] }}">{{ $entity['account']['nickname'] }}</a>.
      </footer>
    </blockquote>
    @endforeach
  </section>
</article>
@endforeach
@endif
