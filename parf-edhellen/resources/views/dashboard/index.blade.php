@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Welcome!')
@section('body')
  <h1>Dashboard</h1>
  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">About you</h2>
        </div>
        <div class="panel-body">
          <ul>
            <li><a href="{{ route('author.profile') }}">Profile</a></li>
          </ul>
        </div>
      </div>

    </div>
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Contributions</h2>
        </div>
        <div class="panel-body">
        </div>
      </div>

    </div>

  </div>
@endsection