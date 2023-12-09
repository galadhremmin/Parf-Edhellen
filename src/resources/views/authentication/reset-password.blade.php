@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Reset password')

@section('body')
  <h1>Reset your password</h1>
  @if ($errors->any())
  <dialog open class="alert alert-warning">
  @foreach ($errors->all() as $error)
  {{ $error }} 
  @endforeach
  </dialog>
  @endif
  <p>
    Pick a new password for {{ $email }}:
  </p>
  <form method="post" action="{{ route('password.complete-reset', ['token' => $token]) }}">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">
    <div class="form-group">
      <label for="new-password-1" class="form-label">New password</label>
      <input type="password" name="password" class="form-control" id="new-password-1">
    </div>
    <div class="form-group">
      <label for="new-password-2" class="form-label">Repeat your new password</label>
      <input type="password" name="password_confirmation" class="form-control" id="new-password-2">
    </div>
    <div class="text-center mt-3">
      <a href="{{ route('login') }}" class="btn btn-secondary">Cancel</a>
      <button type="submit" class="btn btn-primary">Reset password</button>
    </div>
  </form>
  
@endsection 
@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
