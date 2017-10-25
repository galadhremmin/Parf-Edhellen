@extends('_layouts.default')

@section('title', 'New thread - Discussion')
@section('body')
  <h1>New thread</h1>
  
  {!! Breadcrumbs::render('discuss.create') !!}
  
  @include('_shared._errors', [ 'errors' => $errors ])

  <form method="post" action="{{ route('discuss.store') }}">
    <div class="form-group">
      <label for="ed-discuss-subject" class="control-label">Subject</label>
      <input type="text" class="form-control" id="ed-discuss-subject" name="subject">
    </div>
    <div class="form-group">
      <label for="ed-discuss-content" class="control-label">Message</label>
      <textarea id="ed-discuss-content" class="form-control" name="content"></textarea>
    </div>
    <div class="form-group text-right">
      <a href="{{ route('discuss.index') }}" class="btn btn-default">Cancel</a>
      <button type="submit" class="btn btn-primary">
        <span class="glyphicon glyphicon-pencil"></span>
        Save
      </button>
    </div>
    {{ csrf_field() }}
  </form>

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(css/app.discuss.css)">
@endsection
