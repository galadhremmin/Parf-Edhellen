@inject('link', 'App\Helpers\LinkHelper')
@extends('_layouts.default')

@section('title', 'Phrases in '.$language->name)
@section('body')

  {!! Breadcrumbs::render('sentence.public.language', $language->id, $language->name) !!}

  <header>
      @include('sentence.public._header')
      <h2>{{ $language->name }} <span class="tengwar" aria-hidden="true">{{ $language->tengwar }}</span></h2>
  </header>
  <p>
    The texts beneath were composed by Tolkien at some point in his life. They have undergone and 
    are still undergoing extensive analysis by the Tolkien linguistic community. 
  </p>
  <p>
    Click or tap on the header to learn more about them.
  </p>
  <div class="link-blocks">
    @foreach ($sentences as $sentence)
    <blockquote>
      <a class="block-link" href="{{ $link->sentence($language->id, $language->name, $sentence->id, $sentence->name) }}">
        <h3>{{ $sentence->name }}</h3>
        @if(!empty($sentence->description))
        @markdown($sentence->description)
        @endif
      </a>
      <footer>
        {{ $sentence->source }}
        @if ($sentence->account_id)
        by <a href="{{ $link->author($sentence->account_id, $sentence->account_name) }}">{{ $sentence->account_name }}</a>.
        @endif
      </footer>
    </blockquote>
    @endforeach
  </div>
  @if(! empty($neologisms))
    <hr>
    <h2>*Neo-{{ mb_strtolower($language->name) }}</h2>
    <p>
      <em>The texts beneath were <strong>not composed by Tolkien</strong>!</em> They were instead
      composed by fans and students of his elvish languages. We believe the texts are of a sufficient
      standard to be published on our website.
    </p>
    <div class="link-blocks">
      @foreach ($neologisms as $sentence)
      <blockquote>
        <a class="block-link" href="{{ $link->sentence($language->id, $language->name, $sentence->id, $sentence->name) }}">
          <h3>*{{ $sentence->name }}</h3>
          @if(!empty($sentence->description))
          <p>{{ $sentence->description }}</p>
          @endif
        </a>
        <footer>
          {{ $sentence->source }}
          @if ($sentence->account_id)
          by <a href="{{ $link->author($sentence->account_id, $sentence->account_name) }}">{{ $sentence->account_name }}</a>.
          @endif
        </footer>
      </blockquote>
      @endforeach
    </div>
  @endif
@endsection
@section('styles')
  <link href="@assetpath(/css/app.sentences.css)" rel="stylesheet">
@endsection
