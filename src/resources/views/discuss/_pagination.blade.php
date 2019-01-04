@if (count($pages) > 1)
<nav class="text-center">
  <ul class="pagination">
    @if ($current_page > 1)
    <li>
      <a href="?offset={{ $current_page - 1 }}"><span aria-hidden="true">← Older</span></a>
    </li>
    @endif
    @foreach ($pages as $page_number)
    <li class="{{ $page_number == $current_page ? 'active' : '' }}">
      <a href="?offset={{ $page_number }}">{{ $page_number }}</a>
    </li>
    @endforeach
    @if ($current_page + 1 < $no_of_pages)
    <li>
      <a href="?offset={{$current_page + 1}}"><span aria-hidden="true">Newer →</span></a>
    </li>
    @endif
  </ul>
</nav>
@endif
