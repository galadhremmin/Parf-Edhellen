@inject('linker', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Discussion')
@section('body')
  
{!! Breadcrumbs::render('discuss') !!}

<h1>Discuss <span class="tengwar"></span></h1>

@foreach ($groups->keys()->sort() as $group_category)
<h2>{{ $group_category }}</h2>
<div class="link-blocks">
  @foreach ($groups[$group_category] as $group)
  <blockquote>
    <a class="block-link" href="{{ $linker->forumGroup($group->id, $group->name) }}">
      <span class="badge bg-secondary">{{ isset($number_of_threads[$group->id]) ? $number_of_threads[$group->id] : '0' }}</span>
      <h3>
        {{ $group->name }}
        @if ($group->is_readonly)
        <span class="TextIcon TextIcon--lock fs-6" title="Locked"></span>
        @endif
      </h3>
      <p>{{ $group->description }}</p>
      @if (isset($accounts_in_group[$group->id]))
      <div data-inject-module="discuss-groups" data-inject-prop-accounts="@json($accounts_in_group[$group->id])"></div>
      @endif
    </a>
  </blockquote>
  @endforeach
</div>
@endforeach

<div data-inject-module="discuss-feed"></div>

@include('_shared._ad', [
  'ad' => 'forum'
])

@endsection
