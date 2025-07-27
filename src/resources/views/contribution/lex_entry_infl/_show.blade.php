@inject('link', 'App\Helpers\LinkHelper')
@if ($lexicalEntry)
<p>
  <span class="TextIcon TextIcon--info-sign"></span>
  This is a proposed modification of the lexical entry <a href="{{ $link->lexicalEntry($lexicalEntry->id) }}">{{ $lexicalEntry->id }}</a>.
</p>
@endif

<div class="card">
  <div class="card-body">
    <table class="table">
      <thead>
        <tr>
          <th>Word</th>
          <th>Speech</th>
          <th>Inflections</th>
          <th>Rejected?</th>
          <th>Neologism?</th>
          <th>Source</h2>
        </tr>
      </thead>
      <tbody>
        @foreach ($inflections as $inflection)
        <tr>
          <td>{{ $inflection->word }}</td>
          <td>{{ $inflection->speech }}</td>
          <td>{{ implode(', ', $inflection->inflections) }}</td>
          <td>{{ isset($inflection->is_rejected) ? ($inflection->is_rejected ? 'Yes' : 'No') : 'No' }}</td>
          <td>{{ isset($inflection->is_neologism) ? ($inflection->is_neologism ? 'Yes' : 'No') : 'No' }}</td>
          <td>{{ isset($inflection->source) ? $inflection->source : '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
