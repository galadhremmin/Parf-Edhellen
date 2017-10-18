@extends('_layouts.default')

@section('title', 'Versions of ' . ucfirst($word))

@section('body')
<div class="ed-remove-when-navigating">
  <h2>Versions of <em>{{ ucfirst($word) }}</em></h2>
  <p>
    All previous versions including the latest version of this word are available below.
    Comments are inserted between the versions, in descending, chronological order.  
  </p>
  <p>
    <span class="glyphicon glyphicon-info-sign"></span> You can comment on the latest version below.
  </p>
  @foreach ($versions as $v)
    <h3>
      {{ $v->created_at->format('Y-m-d H:i') }}
      @if ($v->is_latest) 
        · <em class="text-info">Latest version</em>
      @elseif (! $v->origin_translation_id)
        · <em class="text-info">Initial version</em>
      @endif
    </h3>
    <div class="well">
      @include('book._gloss', [ 
        'gloss' => $v, 
        'language' => $v->language,
        'disable_tools' => true
      ])
    </div>

    @include('_shared._comments', [
      'entity_id' => $v->id,
      'morph'     => 'translation',
      'enabled'   => $v->is_latest
    ])
    <hr>
  @endforeach
</div>
@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/comment.js)" async></script>
@endsection

@section('styles')
  <style>
    .well blockquote {
      border: none;
    }
  </style>
@endsection
