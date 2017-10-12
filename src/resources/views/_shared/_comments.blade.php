<div id="ed-comments-{{ $morph }}-{{ $entity_id }}-{{ rand() }}" class="ed-comments-container" 
  data-entity-id="{{ $entity_id }}" 
  data-morph="{{ $morph }}" 
  data-account-id="{{ Auth::check() ? Auth::user()->id : '0' }}"
  data-post-enabled="{{ $enabled ? 'true' : 'false' }}"></div>
