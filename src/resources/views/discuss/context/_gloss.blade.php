@foreach ($sections as $data)
  @foreach ($data['entities'] as $gloss)
    @include('book._gloss', [ 'gloss' => $gloss, 'language' => $data['language'] ])
  @endforeach
@endforeach
