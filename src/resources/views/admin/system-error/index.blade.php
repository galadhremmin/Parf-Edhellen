@extends('_layouts.default', ['containerClass' => 'container-fluid'])

@section('title', 'System errors - Administration')
@section('body')

<h1>Service errors</h1>
{!! Breadcrumbs::render('system-error.index') !!}

<section data-inject-module="system-log"
     data-inject-prop-errors-by-week="@json($errorsByWeek)"
     data-inject-prop-error-categories="@json($errorCategories)"
     data-inject-prop-failed-jobs-by-week="@json($failedJobsByWeek)"
     data-inject-prop-failed-jobs-categories="@json($failedJobsCategories)"></section>


<section class="card mb-3 shadow">
   <div class="card-body">
    @include('_shared._audit-trail', [
      'auditTrail' => $auditTrailEntries
    ])
      <div class="d-flex justify-content-between">
         <a class="btn btn-secondary" href="?audit_trail_page={{ max(0, $auditTrailPage - 1) }}">Load newer</a>
         <a class="btn btn-secondary" href="?audit_trail_page={{ $auditTrailPage + 1 }}">Load older</a>
      </div>
   </div>
</div>
<section class="card mb-3 shadow">
   <div class="card-body">
      <h2>Test connectivity</h2>

      @foreach ([ 'IdentifiesPhrasesMonitor' ] as $component)
         <a class="btn btn-secondary" href="{{ route('system-error.connectivity', [ 'component' => $component ]) }}">{{ $component }}</a>
      @endforeach
   </div>
</section>

@endsection
