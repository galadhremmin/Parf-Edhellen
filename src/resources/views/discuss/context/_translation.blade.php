<div class="well">
  <div class="row">
    @foreach ($sections as $data)
      @include('book._language', [
        'language' => $data['language'],
        'glosses'  => $data['glosses'],
        'single'   => false
      ])
    @endforeach
  </div>
</div>
