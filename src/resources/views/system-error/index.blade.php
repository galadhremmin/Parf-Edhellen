@extends('_layouts.default')

@section('title', 'System errors - Administration')
@section('body')

<h1>System errors</h1>
{!! Breadcrumbs::render('system-error.index') !!}

@if (count($errors) < 1)
  <p>
    <em>There are presently no errors registered by the logging service.</em>
  </p>
@else
  <div id="ed-errors"></div>
@endif

<script type="application/json" id="ed-preloaded-errors">
{!! json_encode($errors) !!}
</script>

@endsection

@section('scripts')
  <script type="text/javascript" src="/js/system-errors-admin.js" async></script>
@endsection
