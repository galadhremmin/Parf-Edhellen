@extends('_layouts.default')

@section('title', 'Verify your e-mail address')
@section('body')

<h1>Verify your e-mail address</h1>
{!! Breadcrumbs::render('verification.notice') !!}

<p>
    <strong>You need to verify your email address to access this part of {{ config('app.name') }}.</strong>
    You should have received an e-mail with instuctions on how to verify your e-mail address. If you didn't receive
    the e-mail, we will gladly send you another by pressing the button below.
</p>

<form method="post" action="{{ route('account.resent-verification') }}" class="text-center mt-5">
    @csrf
    <button class="btn btn-secondary" type="submit">Resend verification e-mail</button>
</form>

@endsection
