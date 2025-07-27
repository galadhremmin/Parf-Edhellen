@foreach ($sections as $data)
  @foreach ($data['entities'] as $lexicalEntry)
    @include('book._lexical-entry', [ 'lexicalEntry' => $lexicalEntry, 'language' => $data['language'] ])
  @endforeach
@endforeach
