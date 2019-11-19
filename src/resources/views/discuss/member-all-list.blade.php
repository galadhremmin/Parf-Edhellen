
@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'All contributors - Discussion')
@section('body')
  <h1>All contributors</h1>
  
  {!! Breadcrumbs::render('discuss.member-list') !!}

  <div class="discuss-table">
    @foreach ($members as $member)
      @include('discuss._member-list-item', [
        'account' => $member
      ])
    @endforeach
  </div>

  <div class="text-center">
    {{ $members->links() }}
  </div>

@endsection
