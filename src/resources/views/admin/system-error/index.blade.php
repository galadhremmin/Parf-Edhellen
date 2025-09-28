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
      <h2>Pending jobs</h2>
      @if ($jobsByQueue->isEmpty())
         <em>No pending jobs.</em>
      @else
      <table class="table table-bordered">
         <thead>
            <tr>
               <th>Queue</th>
               <th>Count</th>
            </tr>
         </thead>
         <tbody>
            @foreach ($jobsByQueue as $queue => $count)
               <tr>
                  <td>{{ $queue }}</td>
                  <td>{{ $count }}</td>
               </tr>
            @endforeach
            </tbody>
         </table>
      @endif
      <h2>Job statistics</h2>
      @if ($jobStatsByQueue->isEmpty())
         <em>No job statistics available.</em>
      @else
         <table class="table table-bordered">
            <thead>
               <tr>
                  <th>Queue</th>
                  <th>Jobs</th>
                  <th>Succeeded</th>
                  <th>Failed</th>
                  <th>Retried</th>
                  <th>Avg Exec Time (ms)</th>
                  <th>Max Exec Time (ms)</th>
                  <th>Min Exec Time (ms)</th>
               </tr>
            </thead>
            <tbody>
               @foreach ($jobStatsByQueue as $queue => $stats)
                  <tr>
                     <td>{{ $queue }}</td>
                     <td>{{ $stats['total_count'] ?? 0 }}</td>
                     <td>{{ $stats['success_count'] ?? 0 }}</td>
                     <td>{{ $stats['failed_count'] ?? 0 }}</td>
                     <td>{{ $stats['retry_count'] ?? 0 }}</td>
                     <td>{{ round($stats['avg_execution_time_ms'] ?? 0, 2) }}</td>
                     <td>{{ round($stats['max_execution_time_ms'] ?? 0, 2) }}</td>
                     <td>{{ round($stats['min_execution_time_ms'] ?? 0, 2) }}</td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      @endif
   </div>
</section>

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
