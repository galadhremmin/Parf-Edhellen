<h2>Contributors</h2>
<h3>Active contributors</h3>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Nickname</th>
      <th scope="col">&nbsp;</th>
      <th scope="col">Date registered</th>
      <th scope="col">Words</th>
      <th scope="col">Translations</th>
    </tr>
  </thead>
  <tbody>
  {foreach $activeAuthors as $author}
    <tr>
      <td><a href="profile.page?authorID={$author->id}" class="author-name">{$author->nickname}</a></td>
      <td><span class="tengwar">{$author->tengwar}</a></td>
      <td>{$author->dateRegistered}</td>
      <td>{$author->wordCount}</td>
      <td>{$author->translationCount}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
<h3>All contributors</h3>
<table class="table">
  <thead>
    <tr>
      <th scope="col">Nickname</th>
      <th scope="col">&nbsp;</th>
      <th scope="col">Date registered</th>
    </tr>
  </thead>
  <tbody>
  {foreach $authors as $author}
    <tr>
      <td><a href="profile.page?authorID={$author->id}" class="author-name">{$author->nickname}</a></td>
      <td><span class="tengwar">{$author->tengwar}</a></td>
      <td>{$author->dateRegistered}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
