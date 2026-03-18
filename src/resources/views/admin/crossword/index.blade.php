@extends('_layouts.default')

@section('title', 'Crossword configuration')
@section('body')

<h1>Crossword configuration</h1>

{!! Breadcrumbs::render('crossword.config.index') !!}

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
        <h2>Crossword configuration</h2>
        <form action="{{ route('crossword.config.store') }}" method="post">
            {{ csrf_field() }}
            <div class="form-group">
                <label for="ed-crossword-lexical-entry-group-ids" class="control-label">Eligible lexical entry groups</label>
                <select multiple name="lexical_entry_group_ids[]" size="10" class="form-control" id="ed-crossword-lexical-entry-group-ids">
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
