<blockquote class="daily-sentence">
  <p class="tengwar">
    @foreach ($sentence->fragments as $fragment){{ ($fragment->isPunctuationOrWhitespace() ? '' : ' ') . $fragment->Tengwar }}@endforeach
  </p>
  <p>
    <em>
    @foreach ($sentence->fragments as $fragment){{ ($fragment->isPunctuationOrWhitespace() ? '' : ' ') . $fragment->Fragment }}@endforeach
    </em>
  </p>
  <p>{{$sentence->Description}}</p>
  <footer>{{$sentence->language->Name}} [{{$sentence->Source}}]</footer>
</blockquote>