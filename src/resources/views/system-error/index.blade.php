@extends('_layouts.default')

@section('title', 'System errors - Administration')
@section('body')

<h1>System errors</h1>
{!! Breadcrumbs::render('system-error.index') !!}

@if (count($model['errors']) < 1)
  <p>
    <em>There are presently no errors registered by the logging service.</em>
  </p>
@else
  <div id="ed-errors"></div>
@endif

<script type="application/json" id="ed-preloaded-errors">
{!! json_encode($model) !!}
</script>

@endsection

@section('styles')
<style>
.recharts-wrapper {
  font-family: sans-serif;
}
</style>
@endsection

@section('scripts')
  <script type="text/javascript" src="@assetpath(/js/system-errors-admin.js)" async></script>
@endsection
