@if (count($pages) > 1)
<nav class="text-center">
  <ul class="pagination">
    <li class="page-item{{ $current_page <= 1 ? ' disabled' : '' }}">
      <a href="{{ request()->fullUrlWithQuery(['offset' => $current_page - 1]) }}" class="page-link"><span aria-hidden="true">← Newer</span></a>
    </li>
    @foreach ($pages as $page_number)
    <li class="page-item{{ $page_number == $current_page ? ' active' : '' }}">
      <a href="{{ request()->fullUrlWithQuery(['offset' => $page_number]) }}" class="page-link">{{ $page_number }}</a>
    </li>
    @endforeach
    <li class="page-item{{ $current_page >= $no_of_pages ? ' disabled' : '' }}">
      <a href="{{ request()->fullUrlWithQuery(['offset' => $current_page + 1]) }}" class="page-link"><span aria-hidden="true">Older →</span></a>
    </li>
  </ul>
</nav>
@endif
