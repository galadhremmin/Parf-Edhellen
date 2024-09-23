@extends('_layouts.default')

@section('title', 'Blocked')
@section('body')

<h1>Blocked</h1>

<p>
    Someone operating from your address have made several, repeated attempts to hack this website. If you're using a VPN,
    someone was using your VPN provider to orchestrate these attacks as it'd mask their true location.
    To protect ourselves from these attacks, we've made the difficult decision to block all traffic originating from 
    <strong>{{ $_SERVER['REMOTE_ADDR'] }}</strong> (the address).
</p>
<p>
    Note that addresses are sometimes borrowed from your Internet Service provider. An apartment building can also have
    multiple users on the same address. Unfortunately, we cannot discriminate in these cases. If you nonetheless believe
    you're blocked in error, please reach out to us via Social Media.
</p>

@endsection
