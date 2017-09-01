@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', $author ? 'Edit ' . $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else

  @include('_shared._errors', [ 'errors' => $errors ])

  <form class="form-horizontal" method="post" action="{{ route('author.update-profile', [ 'id' => $author->id ]) }}"
        enctype="multipart/form-data">
    <div class="form-group">
      <label for="ed-author-nickname" class="col-sm-2 control-label">Nickname</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" value="{{ $author->nickname }}" id="ed-author-nickname" name="nickname">
      </div>
    </div>
    <div class="form-group">
      <label for="ed-author-tengwar" class="col-sm-2 control-label">Tengwar</label>
      <div class="col-sm-10">
        <input type="text" class="form-control tengwar" value="{{ $author->tengwar }}" id="ed-author-tengwar" name="tengwar">
      </div>
    </div>
    <div class="form-group">
      <label for="ed-author-avatar" class="col-sm-2 control-label">Avatar (optional)</label>
      <div class="col-sm-10">
        <input type="file" class="form-control" id="ed-author-avatar" name="avatar" accept="image/*">
      </div>
    </div>
    <div class="form-group">
      <label for="ed-author-profile" class="col-sm-2 control-label">Description</label>
      <div class="col-sm-10">
        <textarea name="profile" class="ed-markdown-editor" rows="15">{{ $author->profile }}</textarea>
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-12 text-right">
        <a href="{{ $link->author($author->id, $author->nickname) }}" class="btn btn-default">Cancel</a>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </div>
    {{ csrf_field() }}
  </form>
  @endif
@endsection

@section('scripts')
  <script type="text/javascript" src="/js/markdown.js" async></script>
@endsection
