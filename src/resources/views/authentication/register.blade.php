@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')

@section('body')
  <h1>
    Welcome to our community!
  </h1>
  <h2 class="mt-4">Sign in with social media</h2>

  @if ($error !== null)
  <div class="alert alert-danger">
    <p>
      ⚠️ We received an error from {{ ucfirst($error->provider) }} while trying to log you in. We've recorded the reason and we'll look into it. 
      Please pick another account or try again later. 
      @if (! empty($error->session_id))
      If this error persists, please reach out to us with your session ID: {{ $error->session_id }}.</p>
      @endif
    </p>
  </div>
  @endif

  <p class="mb-3">
    This is the easiest option since you can just use your existing social media account. You won't have to create a password and we won't store
    any sensitive sign in information about you.
  </p>

  <div class="text-center">
  @foreach ($providers as $provider)
  <a href="{{ route('auth.redirect', [ 'providerName' => $provider->name_identifier ]) }}" 
    style="background-image:url(/img/openid-providers/{{ $provider->logo_file_name }})"
    title="Sign in using {{ $provider->name }}." 
    class="ed-authorize-idp">
    {{ $provider->name }}
  </a>
  @endforeach
  </div>

  <p class="mb-4 mt-3">
    Do you miss an identity provider? Please reach out to <em>@parmaeldo</em> on X.
    Please refer to our <a href="{{ route('about.privacy') }}">privacy policy</a> and <a href="{{ route('about.cookies') }}">cookie policy</a> for information about how we use your data.
  </p>

  <hr class="next-overlaps">
  <span>or</span>

  <h2 class="mt-4">Create an account with username and password</h2>
  <p>
    You can sign in with your e-mail address and password. This is an option if you don't have access to the social media above, or prefer not 
    to use them. If you have previously signed in with your social media, you need to create a password to your account before you can sign in.
  </p>
  @if ($errors->any())
  <div class="alert alert-warning">
    There are a few things you need to correct to proceed:
    <ul>
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif
  <form method="post" action="{{ route('auth.register') }}">
    @csrf
    <div class="form-group">
      <label for="password-login-nickname" class="form-label">Nickname</label>
      <input type="text" name="nickname" class="form-control" id="password-login-nickname">
    </div>
    <div class="form-group mt-3">
      <label for="password-login-username" class="form-label">E-mail address</label>
      <input type="text" name="username" class="form-control" id="password-login-username">
    </div>
    <div class="form-group mt-3">
      <label for="password-login-password" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" id="password-login-password">
    </div>
    <div class="form-group mt-3">
      <label for="password-login-password-2" class="form-label">Repeat password</label>
      <input type="password" name="password_confirmation" class="form-control" id="password-login-password-2">
    </div>
    <div class="text-center mt-3">
      <button type="submit" class="btn btn-primary">Create account</button>
    </div>
  </form>
@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
