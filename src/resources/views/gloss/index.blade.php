@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Glossary')
@section('body')
  <h1>Glossary</h1>
  
  {!! Breadcrumbs::render('gloss.index') !!}

  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Functions</h2>
        </div>
        <div class="panel-body">
          <ul>
            <li><a href="{{ route('gloss.create') }}">Add gloss</a></li>
            <li>
              Glossaries by language:
              <ul>
                @foreach ($languages as $language)
                <li><a href="{{ route('gloss.list', [ 'id' => $language->id ]) }}">{{ $language->name }}</a></li>
                @endforeach
              </ul>
            </li>
          </ul>
        </div>
      </div>

    </div>
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Latest activity</h2>
        </div>
        <div class="panel-body">
            <ul>
                @foreach ($latestGlossary as $t)
                <li>
                    <strong><a href="{{ route('gloss.edit', [ 'id' => $t->id ]) }}">{{ $t->word->word }}</a></strong>
                    by 
                    <a href="{{ $link->author($t->account_id, $t->account->nickname) }}">{{ $t->account->nickname }}</a>
                    <span title="{{ $t->updated_at ?: $t->created_at }}}" class="label label-default pull-right date">
                        {{ ($t->updated_at ?: $t->created_at) }}
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
      </div>

    </div>
  </div>
@endsection
