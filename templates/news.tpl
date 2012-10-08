<h2>Activity</h2>
<h3>Last recorded activity ({count($activityList)})</h3>
<table>
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Word</th>
      <th scope="col">Author</th>
      <th scope="col">Last modified</th>
    </tr>
  <thead>
  <tbody>
  {foreach $activityList as $activity}
    <tr>
      <td>{$activity->RelationID}</td>
      <td><a href="#{rawurlencode($activity->Keyword)}">{$activity->Keyword}</a></td>
      <td><a href="/profile.page?authorID={$activity->AccountID}">{$activity->Nickname}</a></td>
      <td>{$activity->CreationDate}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
