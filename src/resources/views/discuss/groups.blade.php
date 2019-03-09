@inject('linker', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  
{!! Breadcrumbs::render('discuss') !!}

<h1>Discussion <span class="tengwar">3D7w#3F</span></h1>
<p></p>

<div class="link-blocks">
  @foreach ($groups as $group)
  <blockquote>
    <a class="block-link" href="{{ $linker->forumGroup($group->id, $group->name) }}">
      <h3>{{ $group->name }}</h3>
      <p>{{ $group->description }}</p>
    </a>
  </blockquote>
  @endforeach
</div>

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-discuss.css)">
@endsection
@section('scripts')
@endsection