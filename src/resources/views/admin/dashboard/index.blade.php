@extends('_layouts.default', ['containerClass' => 'container-fluid'])

@section('title', 'Dashboard - Administration')
@section('body')

<h1>Dashboard</h1>
{!! Breadcrumbs::render('dashboard.index') !!}

<section data-inject-module="system-log"
     data-inject-prop-errors-by-week="@json($errorsByWeek)"
     data-inject-prop-error-categories="@json($errorCategories)"
     data-inject-prop-views-per-day="@json($viewsPerDay)"></section>

<section class="card mb-3 shadow">
   <div class="card-body">
      <h2>Job statistics <small class="text-muted fs-6 fw-normal">last 30 days</small></h2>
      @if ($jobStatsByQueue->isEmpty())
         <em>No job statistics available.</em>
      @else
         <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-1">
            @foreach ($jobStatsByQueue as $stats)
               <div class="col">
                  <div class="card h-100 border-0 bg-body-tertiary">
                     <div class="card-body">
                        <h6 class="card-title text-truncate mb-2" title="{{ $stats->queue }}">{{ $stats->queue }}</h6>
                        <div class="d-flex gap-2 flex-wrap align-items-center mb-2">
                           <span class="badge text-bg-success">{{ number_format($stats->success) }} ok</span>
                           @if ($stats->failed > 0)
                              <span class="badge text-bg-danger">{{ number_format($stats->failed) }} failed</span>
                           @endif
                           @if ($stats->retried > 0)
                              <span class="badge text-bg-warning">{{ number_format($stats->retried) }} retried</span>
                           @endif
                        </div>
                        <div class="progress mb-1" style="height:5px;" role="progressbar" title="{{ $stats->successPct }}% success rate">
                           <div class="progress-bar bg-success" style="width:{{ $stats->successPct }}%"></div>
                           @if ($stats->failed > 0)
                              <div class="progress-bar bg-danger" style="width:{{ $stats->failedPct }}%"></div>
                           @endif
                        </div>
                        <small class="text-muted">{{ number_format($stats->total) }} jobs &bull; {{ $stats->successPct }}% success</small>
                        <div class="d-flex gap-3 mt-2">
                           <small class="text-muted" title="Average execution time">avg {{ $stats->avgDisplay }}</small>
                           <small class="text-muted" title="Minimum execution time">min {{ $stats->minDisplay }}</small>
                           <small class="text-muted" title="Maximum execution time">max {{ $stats->maxDisplay }}</small>
                        </div>
                     </div>
                  </div>
               </div>
            @endforeach
         </div>
      @endif

      <h2 class="mt-3">Pending jobs</h2>
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

      @if ($hasFailedJobs)
         <h2 class="mt-3">Failed jobs</h2>
         <div data-inject-module="failed-jobs"></div>
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
         <a class="btn btn-secondary" href="{{ route('dashboard.connectivity', [ 'component' => $component ]) }}">{{ $component }}</a>
      @endforeach
   </div>
</section>

@endsection
