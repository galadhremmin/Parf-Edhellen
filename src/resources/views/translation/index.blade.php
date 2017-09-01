@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Words')
@section('body')
  <h1>Words</h1>
  
  {!! Breadcrumbs::render('translation.index') !!}

  <div class="row">
    <div class="col-md-6">

      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">Functions</h2>
        </div>
        <div class="panel-body">
          <ul>
            <li><a href="{{ route('translation.create') }}">Add word</a></li>
            <li>
              List by language:
              <ul>
                @foreach ($languages as $language)
                <li><a href="{{ route('translation.list', [ 'id' => $language->id ]) }}">{{ $language->name }}</a></li>
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
                @foreach ($latestTranslations as $t)
                <li>
                    <strong><a href="{{ route('translation.edit', [ 'id' => $t->id ]) }}">{{ $t->word->word }}</a></strong>
                    by 
                    <a href="{{ $link->author($t->account_id, $t->account->nickname) }}">{{ $t->account->nickname }}</a>
                    <span title="{{ $t->updated_at ?: $t->created_at }}}" class="label label-default pull-right">
                        {{ ($t->updated_at ?: $t->created_at)->format('Y-m-d H:i') }}
                    </span>
                </li>
                @endforeach
            </ul>
        </div>
      </div>

    </div>
  </div>
@endsection
