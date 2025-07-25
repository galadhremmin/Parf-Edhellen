@inject('link', 'App\Helpers\LinkHelper')

<blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id="gloss-block-{{ $gloss->id }}"
  class="gloss{{ $gloss->is_canon ? '' : ' contribution' }}">
  <h3 rel="gloss-word" class="gloss-word">
    @if (!$gloss->is_canon || $gloss->is_uncertain)
    <span class="TextIcon TextIcon--asterisk fs-5"></span>
    @endif
    <span itemprop="headline" class="{{ $gloss->is_rejected ? 'rejected' : '' }}">
      {{ $gloss->word }}
    </span>
    @if (isset($gloss->label) && ! empty($gloss->label))
    <span class="gloss-word__neologism">
      <span class="badge rounded-pill badge-sm bg-info position-absolute top-0 start-0 ms-1 translate-middle-y" title="{{ $gloss->label }}">{{ $gloss->label }}</span>
    </span>
    @endif
  </h3>

  <p>
    @if ($gloss->tengwar != null)
    &#32;<span class="tengwar">{{ $gloss->tengwar }}</span>
    @endif
    @if ($gloss->type)
      <span class="word-type" rel="trans-type">{{ $gloss->type }}.</span>
    @endif
    <span rel="trans-gloss" itemprop="keywords">{{ $gloss->all_glosses }}</span>
  </p>

  @if (!isset($hideComments) || !$hideComments)
  <div class="word-comments" rel="trans-comments" itemprop="articleBody">{!! $gloss->comments !!}</div>
  @endif

  @if (isset($gloss->lexical_entry_details) && is_array($gloss->lexical_entry_details))
  @foreach ($gloss->lexical_entry_details as $detail)
  <section class="LexicalEntryDetails details">
    <header><h4>{{ $detail->category }}</h4></header>
    <div class="details__body{{ ! empty($detail->type) ? ' '.$detail->type : '' }}">
      {!! $detail->text !!}
    </div>
  </section>
  @endforeach
  @endif

  <footer class="word-footer">
    @if (is_object($gloss->language))
    {{ $gloss->language->name }}
    @endif

    @if (!empty($gloss->source))
      <span class="word-source" rel="trans-source">[{{ $gloss->source }}]</span>
    @endif
  
    @if (!empty($gloss->etymology))
      <span class="word-etymology" rel="trans-etymology">{{ $gloss->etymology }}.</span>
    @endif
  
    @if ($gloss->lexical_entry_group_id != null)
      Group: <span itemprop="sourceOrganization">{{ $gloss->lexical_entry_group_name }}</span>.
    @endif
  
    Published @date($gloss->created_at, [ 'itemprop' => 'datePublished' ]) by 
    <a href="{{ $link->author($gloss->account_id, $gloss->account_name) }}" itemprop="author" rel="author" title="View profile for {{ $gloss->account_name }}.">
      <span itemprop="name">{{ $gloss->account_name }}</span>
    </a>
  </footer>
</blockquote>
