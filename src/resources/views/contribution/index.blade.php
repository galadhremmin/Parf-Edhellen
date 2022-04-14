@extends('_layouts.default')

@section('title', 'Contributions')
@section('body')
  <h1>Contributions</h1>
  
  {!! Breadcrumbs::render('contribution.index') !!}

  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Contribute</h2>
        </div>
        <div class="panel-body">
          <ul>
            <li><a href="{{ route('contribution.create', ['morph' => 'gloss']) }}">Add gloss</a></li>
            <li><a href="{{ route('contribution.create', ['morph' => 'sentence']) }}">Add phrase</a></li>
          </ul>
          <p>
            <span class="TextIcon TextIcon--info-sign"></span> You can also propose changes to the dictionary. 
            Browse around, and click the <span class="TextIcon TextIcon--edit"></span> button.
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title"></span> Review status</h2>
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
                <td><time datetime="{{ $review->created_at }}">{{ $review->created_at }}</time></td>
                <td>
                  <a href="{{ route('contribution.show', ['contribution' => $review->id]) }}">{{ $review->word }} ({{ $review->sense }})</a></td>
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
