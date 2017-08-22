@inject('link', 'App\Helpers\LinkHelper')

<blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id="translation-block-{{ $gloss->id }}"
  @if (!$gloss->is_canon)
    class="contribution gloss" 
  @else
    class="gloss"
  @endif>
  <h3 rel="trans-word" class="trans-word">
    @if (!$gloss->is_canon || $gloss->is_uncertain || !$gloss->is_latest)
    <a href="/about" title="Unattested, unverified or debatable content." class="neologism">
      <span class="glyphicon glyphicon-asterisk"></span>
    </a>
    @endif
    <span itemprop="headline" class="{{ $gloss->is_rejected ? 'rejected' : '' }}">
      {{ $gloss->word }}
    </span>
    @if (! isset($disable_tools) || ! $disable_tools)
    <a href="{{ $link->translationVersions($gloss->id) }}" class="ed-comments-no">
      <span class="glyphicon glyphicon-comment"></span>
      <span class="no">{{ $gloss->comment_count }}</span>
    </a>
    <a href="{{ $link->translation($gloss->id) }}" class="translation-link">
      <span class="glyphicon glyphicon-share"></span>
    </a>
  
    @if (Auth::check())
    <a href="{{ route('translation.edit', [ 'id' => $gloss->id ]) }}" class="ed-admin-tool" aria-hidden="true" rel="nofollow">
      <span class="glyphicon glyphicon-edit"></span>
    </a>
    @endif
    @endif
  </h3>

  @if ($gloss->tengwar != null)
  &#32;<span class="tengwar">{{ $gloss->tengwar }}</span>
  @endif
  @if ($gloss->type)
    <span class="word-type" rel="trans-type">{{ $gloss->type }}.</span>
  @endif
  <span rel="trans-translation" itemprop="keywords">{{ $gloss->translation }}</span>
  <p class="word-comments" rel="trans-comments" itemprop="articleBody">{!! $gloss->comments !!}</p>

  <footer class="word-footer">
    @if (!empty($gloss->source))
      <span class="word-source" rel="trans-source">[{{ $gloss->source }}]</span>
    @endif
  
    @if (!empty($gloss->etymology))
      <span class="word-etymology" rel="trans-etymology">{{ $gloss->etymology }}.</span>
    @endif
  
    @if ($gloss->translation_group_id != null)
      Group: <span itemprop="sourceOrganization">{{ $gloss->translation_group_name }}</span>.
    @endif
  
    Published <span itemprop="datePublished">{{ $gloss->created_at->format('Y-m-d H:i') }}</span> by 
    <a href="{{ $link->author($gloss->account_id, $gloss->account_name) }}" itemprop="author" rel="author" title="View profile for {{ $gloss->account_name }}.">
      <span itemprop="name">{{ $gloss->account_name }}</span>
    </a>
  </footer>
</blockquote>
