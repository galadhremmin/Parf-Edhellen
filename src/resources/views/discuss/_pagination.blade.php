@if (count($pages) > 1)
<nav>
  <ul class="pagination justify-content-center">
    @if ($current_page > 1)
    <li class="page-item">
      <a href="{{ request()->fullUrlWithQuery(['offset' => $current_page - 1]) }}" class="page-link"><span aria-hidden="true">← Newer</span></a>
    </li>
    @endif
    @foreach ($pages as $page_number)
    <li class="page-item{{ $page_number == $current_page ? ' active' : '' }}">
      <a href="{{ request()->fullUrlWithQuery(['offset' => $page_number]) }}" class="page-link">{{ $page_number }}</a>
    </li>
    @endforeach
    @if ($current_page < $no_of_pages)
    <li class="page-item">
      <a href="{{ request()->fullUrlWithQuery(['offset' => $current_page + 1]) }}" class="page-link"><span aria-hidden="true">Older →</span></a>
    </li>
    @endif
  </ul>
</nav>
@endif
