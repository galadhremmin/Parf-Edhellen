<blockquote class="daily-sentence">
  <p class="tengwar">
    @foreach ($sentence->fragments as $fragment){{ ($fragment->isPunctuationOrWhitespace() ? '' : ' ') . $fragment->tengwar }}@endforeach
  </p>
  <p>
    <em>
    @foreach ($sentence->fragments as $fragment){{ ($fragment->isPunctuationOrWhitespace() ? '' : ' ') . $fragment->fragment }}@endforeach
    </em>
  </p>
  <p>{{$sentence->description}}</p>
  <footer>{{$sentence->language->name}} [{{$sentence->source}}]</footer>
</blockquote>