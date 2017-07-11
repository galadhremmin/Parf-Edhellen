<div id="ed-comments" 
  data-entity-id="{{ $entity_id }}" 
  data-context="{{ $context }}" 
  data-account-id="{{ Auth::check() ? Auth::user()->id : '0' }}"></div>