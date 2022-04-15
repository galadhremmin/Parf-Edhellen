@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Delete phrase - Administration')
@section('body')

<h1>Delete phrase <em>&ldquo;{{ $sentence->name }}&rdquo;</em></h1>
{!! Breadcrumbs::render('sentence.confirm-destroy', $sentence) !!}

<form method="post" action="{{ route('sentence.destroy', ['sentence' => $sentence->id]) }}">
    {{ csrf_field() }}
    {{ method_field('DELETE') }}

    <div class="text-end">
      <div class="btn-group" role="group">
        <a href="{{ $link->sentence($sentence->language_id, $sentence->language->name, $sentence->id, $sentence->name) }}" class="btn btn-secondary">Cancel deletion</a>
        <button type="submit" class="btn btn-danger"><span class="TextIcon TextIcon--trash"></span> Delete</button>
      </div>
    </div>

</form>

@endsection
