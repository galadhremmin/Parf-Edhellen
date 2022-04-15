@if (count($reviews) < 1)
<em>You have no contributions awaiting to be reviewed.</em>
@else
<table class="table table-striped table-hover">
<thead>
  <tr>
    <th style="width:20%">Date submitted</th>
    <th>Title</th>
  </tr>
</thead>
<tbody>
  @foreach ($reviews as $review)
  <tr>
    <td>@date($review->created_at)</td>
    <td>
      <a href="{{ route('contribution.show', ['contribution' => $review->id]) }}">{{ $review->word }} ({{ $review->sense }})</a>
    </td>
  </tr>
  @endforeach
</tbody>
</table>
@endif