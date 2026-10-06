@extends('_layouts.default')

@section('title', 'Confirm it\'s you')

@section('body')
<section class="card mb-4 inbox-code-page">
  <div class="card-body">
    <p class="ed-label mb-1">One more step</p>
    <h1 class="card-title">Check your inbox</h1>
    <p class="ed-panel__lead">
      You're signing in with {{ $providerName ?? 'an account' }} as <strong>{{ $account->nickname }}</strong>, which is linked
      to your principal account. We've sent a six-digit code to <strong>{{ $maskedEmail }}</strong> to make sure it's you.
      You only need to do this once for this account.
    </p>

    @if (session('status'))
    <p class="ed-ui">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ route('auth.confirm-sign-in.check') }}">
      @csrf
      <label for="sign-in-code" class="form-label">Code</label>
      <input type="text" name="code" id="sign-in-code" class="form-control inbox-code-page__code @error('code') is-invalid @enderror"
        inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]*" maxlength="16" autofocus required>
      @error('code')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror

      <div class="ed-panel__footer">
        <p class="ed-panel__note">The code works for {{ $lifetime }} minutes, and only in this browser.</p>
        <button type="submit" class="btn btn-secondary">Sign in</button>
      </div>
    </form>

    <form method="post" action="{{ route('auth.confirm-sign-in.resend') }}" class="mt-3">
      @csrf
      <span class="ed-ui">No e-mail?</span>
      <button type="submit" class="btn btn-link btn-sm p-0 align-baseline">Send a new code</button>
    </form>
  </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
