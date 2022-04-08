@extends('_layouts.default')

@section('title', __('word-finder.config.title'))
@section('body')

<h1>@lang('word-finder.config.title')</h1>
  
{!! Breadcrumbs::render('word-finder.config.index') !!}

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('word-finder.config.store') }}" method="post">
    {{ csrf_field() }}
    <div class="form-group">
        <label for="ed-word-finder-gloss-group-ids" class="control-label">Eligible gloss groups</label>
        <select multiple name="gloss_group_ids[]" size="10" class="form-control" id="ed-word-finder-gloss-group-ids">
            @foreach ($all_gloss_groups as $gloss_group)
            <option value="{{ $gloss_group->id }}" {{ isset($selected_gloss_group_ids[(string) $gloss_group->id]) ? "selected" : '' }}>{{ $gloss_group->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="text-end">
        <button class="bnt btn-primary" type="submit">Save</button>
    </div>
</form>

@endsection
