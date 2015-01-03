<h2>Elvish phrases</h2>
<p>Tolkien composed many elvish phrases and poems throughout his lifetime. Who among us wouldn't recall A Elbereth Gilthoniel from the Lord of the Rings? Much of what we know (or think we know) about the elvish languages has actually been derived from these fragments, whether from the original triology or in posthumously published documents.</p>
<p>The list beneath contains some of the more well-known phrases in Sindarin and Quenya, including a word-by-word analysis of the sentences.</p>

<div data-module="sentence">
  {foreach from=$sentences item=sentence}
  <blockquote>
    <h3>{$sentence->sentence}</h3>
    <p>{$sentence->description}</p>
    <footer>{$sentence->language} [{$sentence->source}]</footer>
  </blockquote>
  
  {foreach from=$sentence->fragments item=fragment}
  {if !is_numeric($fragment->translationID)}
  {continue}
  {/if}
  <div class="modal" id="fragment-dialogue-{$fragment->fragmentID}">
    <div class="modal-dialog">
      <div class="modal-content"> 
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title"><span class="ed-word"></span> &gt; {$fragment->fragment}</h4>
        </div>
        <div class="modal-body">
          {if !empty($fragment->comments)}
          <p class="ed-fragment">{$fragment->comments}</p>
          {/if}

          <div class="ed-definition">
            <p>
              <strong class="ed-word"></strong>
              <span class="ed-translation"></span>
            </p>
            <p class="ed-comments"></p>
            <p>
              [<span class="ed-source"></span>]
            </p>
          </div>
          
          <div class="ed-navigation row">
            <div class="col-xs-6 col-sm-6">
            {if $fragment->previousFragmentID > 0}
              <button type="button" class="btn btn-default btn-sm ed-fragment-navigation-back" data-neighbour-fragment="{$fragment->previousFragmentID}"><span class="glyphicon glyphicon-chevron-left"></span> <span class="word">previous</span></button>
            {/if}
            </div>
            <div class="col-xs-6 col-sm-6">
            {if $fragment->nextFragmentID > 0}
              <button type="button" class="btn btn-default btn-sm ed-fragment-navigation-forward" data-neighbour-fragment="{$fragment->nextFragmentID}"><span class="glyphicon glyphicon-chevron-right"></span> <span class="word">next</span></button>
            {/if}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  {/foreach}
  {/foreach}
</div>
