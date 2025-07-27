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

<div class="card shadow">
    <div class="card-body">
        <h2>Sage configuration</h2>
        <form action="{{ route('word-finder.config.store') }}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="ed-word-finder-gloss-group-ids" class="control-label">Eligible gloss groups</label>
                <select multiple name="lexical_entry_group_ids[]" size="10" class="form-control" id="ed-word-finder-gloss-group-ids">
                    @foreach ($all_lexical_entry_groups as $group)
                    <option value="{{ $group->id }}" {{ isset($selected_lexical_entry_group_ids[(string) $group->id]) ? "selected" : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-end mt-3">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection
