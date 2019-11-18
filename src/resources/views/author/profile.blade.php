@extends('_layouts.default')

@section('title', $author ? $author->nickname : 'Missing account')

@section('body')
  @if ($author === null)
    This is not the droid you are looking for.
  @else
    <div class="view-profile"
         data-inject-module="dashboard-profile"
         data-inject-prop-container="Profile"
         data-inject-prop-account="@json($author)"></div>
      </div>
      <div class="col-md-4 col-sm-12">
        <h2 class="hidden-md hidden-lg">Statistics</h2>
        <table class="table striped">
          <tbody>
            <tr>
              <th>Words</th>
              <td class="text-right">{{ $stats['noOfWords'] }}</td>
            </tr>
            <tr>
              <th><a href="{{ route('author.glosses', ['id' => $author->id]) }}">Glosses</a></th>
              <td class="text-right">{{ $stats['noOfGlosses'] }}</td>
            </tr>
            <tr>
              <th><a href="{{ route('author.sentences', ['id' => $author->id]) }}">Phrases</a></th>
              <td class="text-right">{{ $stats['noOfSentences'] }}</td>
            </tr>
            <tr>
              <th><a href="{{ route('author.posts', ['id' => $author->id]) }}">Posts</a></th>
              <td class="text-right">{{ $stats['noOfPosts'] }}</td>
            </tr>
            <tr>
              <th><span class="glyphicon glyphicon-thumbs-up"></span> Thanks</th>
              <td class="text-right">{{ $stats['noOfThanks'] }}</td>
            </tr>
            <tr>
              <th><span class="glyphicon glyphicon-tag"></span> Flashcards</th>
              <td class="text-right">{{ $stats['noOfFlashcards'] }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  @endif
  <hr>

  @include('discuss._standalone', [
    'entity_id'   => $author->id,
    'entity_type' => 'account'
  ])
@endsection

@section('styles')
@include('discuss._css')
@endsection