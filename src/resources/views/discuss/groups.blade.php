@inject('linker', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  
{!! Breadcrumbs::render('discuss') !!}

<h1>Discussion <span class="tengwar"></span></h1>

<div class="link-blocks">
  @foreach ($groups as $group)
  <blockquote>
    <a class="block-link" href="{{ $linker->forumGroup($group->id, $group->name) }}">
      <span class="label label-default">{{ $number_of_threads[$group->id] }}</span>
      <h3>{{ $group->name }}</h3>
      <p>{{ $group->description }}</p>
      @if (isset($accountsInGroup[$group->id]))
      <div data-inject-module="discuss-groups" data-inject-prop-accounts="@json($accountsInGroup[$group->id])"></div>
      @endif
    </a>
  </blockquote>
  @endforeach
</div>

<div data-inject-module="discuss-feed"></div>

@include('_shared._ad', [
  'ad' => 'forum'
])

@endsection
