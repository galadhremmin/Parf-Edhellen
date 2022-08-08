@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Logging in')

@section('body')
  <h1>Logging in</h1>
  <div class="container">
    <div class="alert alert-info">
      <span class="TextIcon TextIcon--info-sign bg-info"></span>
      We do not store usernames and passwords; we trust so-called identity providers instead
      @if (! empty($providers)) (like {{ $providers[0]->name }}). @else . @endif
      Your profile will be linked to the provider you choose.
    </div>
    <p>Welcome! Please choose an identity provider to log in:</p>
  </div>

  <div class="text-center">
  @foreach ($providers as $provider)
  <a href="{{ $link->authRedirect($provider->name_identifier) }}" 
    style="background-image:url(/img/openid-providers/{{ $provider->logo_file_name }})"
    title="Log in using {{ $provider->name }}." 
    class="ed-authorize-idp">
    {{ $provider->name }}
  </a>
  @endforeach
  </div>

  <hr>

  <p>
    Do you miss an identity provider? Please reach out to <em>@parmaeldo</em> on Twitter.
    Please refer to our <a href="{{ route('about.privacy') }}">privacy policy</a> and <a href="{{ route('about.cookies') }}">cookie policy</a> for information about how we use your data.
  </p>

@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
