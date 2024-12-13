@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Glossary')
@section('body')
  <h1>Glossary</h1>
  
  {!! Breadcrumbs::render('gloss.index') !!}

  <div class="row">
    <div class="col-md-6">

      <div class="card shadow-lg">
        <div class="card-body">
          <h2>Explore the glossary</h2>
          <ul>
            <li>
              Glossaries by language:
              <ul>
                @foreach ($languages as $language)
                <li><a href="{{ route('gloss.list', [ 'id' => $language->id ]) }}">{{ $language->name }}</a></li>
                @endforeach
              </ul>
            </li>
          </ul>
          <a href="{{ $link->contributeGloss() }}" class="btn btn-primary">Add gloss</a>
        </div>
      </div>

    </div>
    <div class="col-md-6">

      <div class="card shadow-lg">
        <div class="card-body">
          <h2>Latest activity</h2>
            <ul>
                @foreach ($latestGlosses as $t)
                <li>
                    <strong><a href="{{ $link->contributeGloss($t->id) }}">{{ $t->word->word }}</a></strong>
                    by 
                    <a href="{{ $link->author($t->account_id, $t->account->nickname) }}">{{ $t->account->nickname }}</a>
                    <span title="{{ $t->updated_at ?: $t->created_at }}" class="badge bg-secondary float-end date">
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
