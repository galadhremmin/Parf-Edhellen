@extends('_layouts.default')

@section('title', $author ? 'Edit ' . $author->Nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else

    @if (count($errors) > 0)
      <div class="alert alert-danger">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form class="form-horizontal" method="post" action="{{ route('author.update-profile', [ 'id' => $author->AccountID ]) }}">
      <div class="form-group">
        <label for="ed-author-nickname" class="col-sm-2 control-label">Nickname</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" value="{{ $author->Nickname }}" id="ed-author-nickname" name="nickname">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-author-tengwar" class="col-sm-2 control-label">Tengwar</label>
        <div class="col-sm-10">
          <input type="text" class="form-control tengwar" value="{{ $author->Tengwar }}" id="ed-author-tengwar" name="tengwar">
        </div>
      </div>
      <div class="form-group">
        <label for="ed-author-profile" class="col-sm-2 control-label">Tengwar</label>
        <div class="col-sm-10">
          <textarea class="form-control" id="ed-author-profile" name="profile" rows="15">{{ $author->Profile ?? '' }}</textarea>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <a href="{{ route('') }}" class="btn btn-primary">Update</a>
          <button type="button" class="btn btn-default">Cancel</button>
        </div>
      </div>
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>
  @endif
@endsection
