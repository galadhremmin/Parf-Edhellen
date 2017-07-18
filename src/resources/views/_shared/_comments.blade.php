<div id="ed-comments-{{ $context }}-{{ $entity_id }}-{{ rand() }}" class="ed-comments-container" 
  data-entity-id="{{ $entity_id }}" 
  data-context="{{ $context }}" 
  data-account-id="{{ Auth::check() ? Auth::user()->id : '0' }}"
  data-post-enabled="{{ $enabled ? 'true' : 'false' }}"></div>