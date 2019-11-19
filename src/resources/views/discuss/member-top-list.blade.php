@extends('_layouts.default')

@section('title', 'Contributors - Discussion')
@section('body')
  <h1>Contributors</h1>
  
  {!! Breadcrumbs::render('discuss.members') !!}

  <p>
    Tolkien's languages appeals to a wide demography as the wide regional distribution of Parf Edhellen's visitors can attest. 
    Our members come from all over the world, and they all have one thing in common: fascination with the beauty of the elvish languages.
  </p>
  <p>
    On this page, we display top activity and contribution metrics by categories we believe you might be interested in. This page was
    generated <span class="date">{{ $data['created_at'] }}</span> and will be updated automatically <span class="date">{{ $data['expires_at'] }}</span>.
  </p>

  @foreach ($data['categories'] as $category)
  <h2>@lang('discuss.member-list.category.'.$category)</h2>
  @if (isset($data['growth'][$category]))
  <div id="ed-discuss-growth-chart-{{ $category }}" data-inject-module="statistics-chart" data-inject-prop-data="{{ json_encode($data['growth'][$category]) }}"></div>
  @endif
  <div class="discuss-table">
    @foreach ($data[$category] as $item)
      @include('discuss._member-list-item', [
        'account'     => $data['accounts'][$item->id],
        'detailsView' => 'discuss._member-list-top-details',
        'details'     => [
          'item'  => $item,
          'total' => isset($data['totals'][$category]) ? $data['totals'][$category] : 0
        ]
      ])
    @endforeach
  </div>
  @endforeach

  <hr>
  
  <p>
    Looking for someone specific? You can <a href="{{ route('discuss.member-list') }}">browse all of our contributors.</a>
  </p>

@endsection
