@extends('_layouts.default')

@section('title', 'Confirm your e-mail address')

@section('body')
<section class="card mb-4 inbox-code-page">
  <div class="card-body">
    <p class="ed-label mb-1">Almost there</p>
    <h1 class="card-title">Confirm your e-mail address</h1>
    <p class="ed-panel__lead">
      We've sent an e-mail to <strong>{{ $user->email }}</strong>. Enter the six-digit code from it below, or press the
      button in the e-mail. Then your account is ready.
    </p>

    @if (session('status'))
    <p class="ed-ui">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ route('verification.check') }}">
      @csrf
      <label for="verification-code" class="form-label">Code</label>
      <input type="text" name="code" id="verification-code" class="form-control inbox-code-page__code @error('code') is-invalid @enderror"
        inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]*" maxlength="16" autofocus required>
      @error('code')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror

      <div class="ed-panel__footer">
        <p class="ed-panel__note">
          Until then, you can keep <a href="{{ route('home') }}">browsing the dictionary</a>; your profile, contributions
          and word lists open once you've confirmed.
        </p>
        <button type="submit" class="btn btn-secondary">Confirm</button>
      </div>
    </form>

    <form method="post" action="{{ route('account.resent-verification') }}" class="mt-3">
      @csrf
      <span class="ed-ui">No e-mail?</span>
      <button type="submit" class="btn btn-link btn-sm p-0 align-baseline">Send another</button>
      <span class="ed-ui ms-2">Wrong address?</span>
      <a href="{{ route('logout') }}" class="btn btn-link btn-sm p-0 align-baseline">Sign out</a>
    </form>
  </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
