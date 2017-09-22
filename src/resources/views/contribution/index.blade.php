@extends('_layouts.default')

@section('title', 'Contributions')
@section('body')
  <h1>Contributions</h1>
  
  {!! Breadcrumbs::render('contribution.index') !!}

  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-tree-deciduous"></span> Contribute</h2>
        </div>
        <div class="panel-body">
          <ul>
            <li><a href="{{ route('contribution.create') }}">Add word</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"><span class="glyphicon glyphicon-hourglass"></span> Review status</h2>
        </div>
        <div class="panel-body">
          @if (count($reviews) < 1)
          <em>You have no contributions awaiting to be reviewed.</em>
          @else
          <table class="table table-striped table-hover">
            <thead>
              <tr>
                <th>Date</th>
                <th>Word</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($reviews as $review)
              <tr>
                <td>{{ $review->created_at->format('Y-m-d H:i') }}</td>
                <td>
                  <a href="{{ route('contribution.show', ['id' => $review->id]) }}">{{ $review->word }} ({{ $review->sense }})</a></td>
                <td>
                  @if ($review->is_approved === null)
                  <span class="text-info">pending</span>
                  @elseif ($review->is_approved) 
                  <span class="text-success">approved</span>
                  @else
                  <span class="text-danger" title="{{ $review->justification }}">rejected</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
          @endif
        </div>
      </div>

    </div>
  </div>
  
@endsection
