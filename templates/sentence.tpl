<h2>Elvish phrases</h2>
<p>Tolkien composed many elvish phrases and poems throughout his lifetime. Who among us wouldn't recall A Elbereth Gilthoniel from the Lord of the Rings? Much of what we know (or think we know) about the elvish languages has actually been derived from these fragments, whether from the original triology or in posthumously published documents.</p>
<p>The list beneath contains some of the more well-known phrases in Sindarin and Quenya, including a word-by-word analysis of the sentences.</p>

<div data-module="sentence">
  {foreach from=$sentences item=sentence}
  <h3>{$sentence->sentence}</h3>
  <p>{$sentence->description}</p>
  
  <div class="dialogues">
    {foreach from=$sentence->fragments item=fragment}
    {if !is_numeric($fragment->translationID)}
    {continue}
    {/if}
    <div>
      <h4>{$fragment->fragment}</h4>
      {if !empty($fragment->comments)}
      <p>{$fragment->comments}</p>
      {/if}
      <p>
        
      </p>
    </div>
    {/foreach}
  </div>
  {/foreach}
</div>
