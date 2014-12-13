<h2>Elvish phrases</h2>
<p>Tolkien composed many elvish phrases and poems throughout his lifetime. Who among us wouldn't recall A Elbereth Gilthoniel from the Lord of the Rings? Much of what we know (or think we know) about the elvish languages has actually been derived from these fragments, whether from the original triology or in posthumously published documents.</p>
<p>The list beneath contains some of the more well-known phrases in Sindarin and Quenya, including a word-by-word analysis of the sentences.</p>

<div data-module="sentence">
  {foreach from=$sentences item=sentence}
  <h3>{$sentence->sentence}</h3>
  <p>{$sentence->description}</p>
  
  {foreach from=$sentence->fragments item=fragment}
  {if !is_numeric($fragment->translationID)}
  {continue}
  {/if}
  <div class="modal fade" id="fragment-dialogue-{$fragment->fragmentID}">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title"><span class="ed-word"></span> &gt; {$fragment->fragment}</h4>
        </div>
        <div class="modal-body">
          {if !empty($fragment->comments)}
          <p>{$fragment->comments}</p>
          {/if}
          <p class="ed-translation"></p>
          <p class="ed-comments"></p>
          <p>
            [<span class="ed-source"></span>]
          </p>
        </div>
      </div>
    </div>
  </div>
  {/foreach}
  {/foreach}
</div>
