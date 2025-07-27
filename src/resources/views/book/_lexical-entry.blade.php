@inject('link', 'App\Helpers\LinkHelper')

<blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id="gloss-block-{{ $lexicalEntry->id }}"
  class="gloss{{ $lexicalEntry->is_canon ? '' : ' contribution' }}">
  <h3 rel="gloss-word" class="gloss-word">
    @if (!$lexicalEntry->is_canon || $lexicalEntry->is_uncertain)
    <span class="TextIcon TextIcon--asterisk fs-5"></span>
    @endif
    <span itemprop="headline" class="{{ $lexicalEntry->is_rejected ? 'rejected' : '' }}">
      {{ $lexicalEntry->word }}
    </span>
    @if (isset($lexicalEntry->label) && ! empty($lexicalEntry->label))
    <span class="gloss-word__neologism">
      <span class="badge rounded-pill badge-sm bg-info position-absolute top-0 start-0 ms-1 translate-middle-y" title="{{ $lexicalEntry->label }}">{{ $lexicalEntry->label }}</span>
    </span>
    @endif
  </h3>

  <p>
    @if ($lexicalEntry->tengwar != null)
    &#32;<span class="tengwar">{{ $lexicalEntry->tengwar }}</span>
    @endif
    @if ($lexicalEntry->type)
      <span class="word-type" rel="trans-type">{{ $lexicalEntry->type }}.</span>
    @endif
    <span rel="trans-gloss" itemprop="keywords">{{ $lexicalEntry->all_glosses }}</span>
  </p>

  @if (!isset($hideComments) || !$hideComments)
  <div class="word-comments" rel="trans-comments" itemprop="articleBody">{!! $lexicalEntry->comments !!}</div>
  @endif

  @if (isset($lexicalEntry->lexical_entry_details) && is_array($lexicalEntry->lexical_entry_details))
  @foreach ($lexicalEntry->lexical_entry_details as $detail)
  <section class="LexicalEntryDetails details">
    <header><h4>{{ $detail->category }}</h4></header>
    <div class="details__body{{ ! empty($detail->type) ? ' '.$detail->type : '' }}">
      {!! $detail->text !!}
    </div>
  </section>
  @endforeach
  @endif

  <footer class="word-footer">
    @if (is_object($lexicalEntry->language))
    {{ $lexicalEntry->language->name }}
    @endif

    @if (!empty($lexicalEntry->source))
      <span class="word-source" rel="trans-source">[{{ $lexicalEntry->source }}]</span>
    @endif
  
    @if (!empty($lexicalEntry->etymology))
      <span class="word-etymology" rel="trans-etymology">{{ $lexicalEntry->etymology }}.</span>
    @endif
  
    @if ($lexicalEntry->lexical_entry_group_id != null)
      Group: <span itemprop="sourceOrganization">{{ $lexicalEntry->lexical_entry_group_name }}</span>.
    @endif
  
    Published @date($lexicalEntry->created_at, [ 'itemprop' => 'datePublished' ]) by 
    <a href="{{ $link->author($lexicalEntry->account_id, $lexicalEntry->account_name) }}" itemprop="author" rel="author" title="View profile for {{ $lexicalEntry->account_name }}.">
      <span itemprop="name">{{ $lexicalEntry->account_name }}</span>
    </a>
  </footer>
</blockquote>
