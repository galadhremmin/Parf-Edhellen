@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Logging in')

@section('body')
  <h1>Logging in</h1>
  <p>
    Greetings traveller! Would you like to join our community? You are more than welcome to do!
  </p>
  <blockquote>
    We are not interested in your personal information. We use these
    services to <em>confirm your identity</em>. That is all. The only information we save is 
    your e-mail address, which is kept secret in our database.
  </blockquote>
  <p>
    We believe in protecting your privacy, and this solution enables us to create accounts without 
    storing sensitive information, like passwords. These third party identity providers 
    (as they are called) assert your identity by confirming that you have an account with them. 
    It works a bit like a passport, where your country provides the identity!
  </p>

  <hr>

  @foreach ($providers as $provider)
  <a href="{{ $link->authRedirect($provider->name_identifier) }}" 
    style="background-image:url(/img/openid-providers/{{ $provider->logo_file_name }})"
    title="Log in using {{ $provider->name }}." 
    class="ed-authorize-idp">
    {{ $provider->name }}
  </a>
  @endforeach

  <hr>

  Do you miss your community? If you would contact <em>@parmaeldo</em> on Twitter, I'll see what I can do!
@endsection
@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
