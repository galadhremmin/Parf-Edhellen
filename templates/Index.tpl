<div class="jumbotron">
<h1>Mae govannen!</h1>
<p>Parf Edhellen&mdash;an <em>Elvish Book</em>&mdash; is a free dictionary service, consisting of imported glosses from <em>Hiswelókë Sindarin Dictionary</em>, 
<em>Ardalambion Quenya Wordlists</em> and <a href="about.page?browseTo=wordlist">many, many more</a>. Please enter your search query in the search field above to begin your journey.</p>
</div>

<hr>

<div class="row">
  <div class="col-xs-12 col-sm-4">
    <h4>About me</h4>
    <p>My name is Leonard (also known as <em>Aldaleon</em> of the Mellonath Daeron)
      and I develop and maintain this elvish dictionary. If you want to get in touch with me, please tweet me at
      <a href="https://twitter.com/parmaeldo" target="_blank">@parmaeldo on Twitter</a>.</p>
    <p>Please consider donating.
      For more information, please go to our <a href="/donations.page">donation page</a>.</p>
    <hr class="visible-xs">
  </div>
  <div class="col-xs-12 col-sm-4">
    <h4>Community activity</h4>
    <ul class="list-group">
    {foreach $reviews as $review}
      <li class="list-group-item">
        {date_format($review->dateCreated, 'Y-m-d H:i')}
        <a href="/index.page#translationID={$review->translationID}">{$review->word}</a>
        by <a href="/profile.page?authorID={$review->authorID}">{$review->authorName}</a>
      </li>
    {/foreach}
    </ul>
    <hr class="visible-xs">
  </div>
  <div class="col-xs-12 col-sm-4">
    <h4>Random elvishness</h4>
    <blockquote>
      <p class="tengwar">
      {$sentence->sentenceTengwar}
      </p>
      <p>
        {$sentence->sentence}
      </p>
      <footer>{$sentence->language} [{$sentence->source}]</footer>
    </blockquote>
  </div>
</div>