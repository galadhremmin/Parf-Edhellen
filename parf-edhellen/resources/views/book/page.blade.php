@extends('_layouts.default')

@section('title', 'About')

@section('body')

<div id="translation-entry" data-module="translation">
  <div class="row">
    @foreach ($sections as $language)
      @include('book._language', $language)
    @endforeach
  </div>
</div>


<!--
<div id="translation-entry" data-module="translation">
{* iterate through all translations where the key of the array defines the associated language. The counter 
   uniquely identifies each translation entry, so that the user might levelage this information while navigating
   the results *}
{counter start=-1 print=false}
<div class="row">
  {foreach from=$translations key=language item=translationsForLanguage}
  
  {/foreach}
</div>

{* Show a message if no such word exists *}
{if $senses|@count < 1}
<div class="row">
  <h3>Forsooth! I can't find what you're looking for!</h3>
  <p>The word <em>{$term}</em> hasn't been recorded for any of the languages.</p>
</div>
{/if}

</div>
-->

@endsection