@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Forgot password')

@section('body')
  <h1>Forgot password</h1>
  @if ($errors->any())
  <dialog open class="alert alert-warning">
  @foreach ($errors->all() as $error)
  {{ $error }} 
  @endforeach
  </dialog>
  @endif
  @if (! empty($status))
  <dialog open class="alert alert-info">
    {{ $status }}
  </dialog>
  @endif
  <p>
    Enter your e-mail address in the text area below and we'll send you an e-mail with instructions on how to reset your password.
  </p>
  <form method="post" action="{{ route('auth.reset-password') }}">
    @csrf
    <div class="form-group">
      <label for="password-login-username" class="form-label">E-mail address</label>
      <input type="text" name="username" class="form-control" id="password-login-username">
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
