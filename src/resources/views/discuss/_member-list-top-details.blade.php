@if (isset($data['item']->number_of_items)) 
{{ $data['item']->number_of_items }}
<p class="total-indicator">
  {{ round($data['item']->number_of_items / $data['total'] * 100, 2) }} % of total.
</p>
@endif
