@extends('_layouts.default')

@section('title', 'Test identity provider')

@section('body')
<section class="card mb-4 test-identity-provider">
  <div class="card-body">
    <p class="ed-label mb-1">Local testing only</p>
    <h1 class="card-title">Sign in as anyone</h1>
    <p class="ed-panel__lead">
      The test identity provider says whatever you tell it. The same e-mail address signs back into the same account;
      give a different identity to make a second account on that address, for trying out linking.
    </p>

    <form method="get" action="{{ $callbackUrl }}" class="test-identity-provider__form">
      <div>
        <label for="test-idp-email" class="form-label">E-mail address</label>
        <input type="email" name="email" id="test-idp-email" class="form-control" required autofocus>
      </div>
      <div>
        <label for="test-idp-name" class="form-label">Name</label>
        <input type="text" name="name" id="test-idp-name" class="form-control" placeholder="Tester">
      </div>
      <div>
        <label for="test-idp-identity" class="form-label">Identity</label>
        <input type="text" name="identity" id="test-idp-identity" class="form-control" placeholder="Defaults to the e-mail address">
        <div class="form-text">The provider's id for the person. Change it to be someone else on the same address.</div>
      </div>
      <div class="form-check">
        <input type="checkbox" name="verified" value="1" id="test-idp-verified" class="form-check-input">
        <label for="test-idp-verified" class="form-check-label">The provider vouches for the address, like Google's <code>email_verified</code></label>
      </div>

      <div class="ed-panel__footer">
        <p class="ed-panel__note">Unvouched addresses go through our own e-mail checks, as they would with Discord or Microsoft.</p>
        <button type="submit" class="btn btn-secondary">Sign in</button>
      </div>
    </form>
  </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="@assetpath(style-auth.css)">
@endsection
