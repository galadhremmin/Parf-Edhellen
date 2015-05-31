<div data-module="dashboard">
	<h2>Dashboard</h2>
	<p>Test.</p>
	
	<div class="row">
	  
	  <div class="col-sm-6">
	    <h3>Translations</h3>
	    <p></p>
	    <ul class="list-group">
	      {if count($translations) < 1}
	      You have published no translations.
	      {else}
	      {foreach $translations as $translation}
	      <li class="list-group-item" id="translation-{$translation->id}">
	        <a href="/translate-form.page?translationID={$translation->id}">{$translation->word}</a>
	        &mdash;
	        {$translation->translation}
	        <span class="label label-default pull-right">{date_format($translation->dateCreated, 'Y-m-d H:i')}</span>
	      </li>
	      {/foreach}
	      {/if}
	    </ul>
	    <!--
	    <nav>
	      <ul class="pagination">
	        <li>
	          <a href="#" aria-label="Previous">
	            <span aria-hidden="true">&laquo;</span>
	          </a>
	        </li>
	        <li><a href="#">1</a></li>
	        <li><a href="#">2</a></li>
	        <li><a href="#">3</a></li>
	        <li><a href="#">4</a></li>
	        <li><a href="#">5</a></li>
	        <li>
	          <a href="#" aria-label="Next">
	            <span aria-hidden="true">&raquo;</span>
	          </a>
	        </li>
	      </ul>
	    </nav>
	    -->
	    <a href="/translate-form.page" role="button" class="btn btn-default btn-sm pull-right"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span> Create</a>
	  </div>
	  <div class="col-sm-6">
	    <h3>Favourites</h3>
	    <p></p>
	    <ul class="list-group">
	      {if count($favourites) < 1}
	      You have no favourites.
	      {else}
	      {foreach $favourites as $favourite}
	      <li class="list-group-item" id="favourite-{$favourite->id}">
	        <a href="/index.page#translationID={urlencode($favourite->translation->id)}">{$favourite->translation->word}</a>
	        <a class="pull-right favourite-delete" href="#" data-favourite-id="{$favourite->id}"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
	      </li>
	      {/foreach}
	      {/if}
	    </ul>
	  </div>
	  
	</div>
	
	<div class="row">
	  
	  <div class="col-sm-6">
	    <h3>Comments <span class="badge">40</span></h3>
	    <p></p>
	  </div>
	  <div class="col-sm-6">
	    <h3>Statistics</h3>
	
	  </div>
	  
	</div>
</div>