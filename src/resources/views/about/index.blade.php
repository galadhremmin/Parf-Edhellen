@extends('_layouts.default')

@section('title', 'About')
@section('body')

<h1>About <span class="tengwar" title="About Parf Edhellen">  </span></h1>
<p>Welcome to the <em>Parf Edhellen</em>, The collaborative dictionary dedicated to Tolkien's amazing languages. 
<em>Parf Edhellen</em> means &ldquo;Elvish Book&rdquo; in Sindarin, the noble language of elves and men. But the 
dictionary contains all elvish languages, including Telerin and Quenya, with words imported from a variety of 
sources, all of which have been carefully compiled by researchers of Tolkien's languages.</p>
<p>... and there is a lot! Tolkien was an amazing linguist; a skillset he employed throughout his life as he devised
beautiful (and not so beautiful!) languages for his legendarium. If you are curious about Sauron's foul vernacular,
you will also find his despicable language &ldquo;Black Speech&rdquo; within the dictionary.</p>
<p><em>Parf Edhellen</em> is open source, and actively developed and maintained by Leonard &ldquo;Aldaleon&rdquo;, 
a Tolkien-fan, and language enthusiast.

<a name="wordlist"></a>
<h2>Credits &amp; Sources</h2>
<p><em>Parf Edhellen</em> imported its definition from the excellent dictionaries listed below.
  Please note that discrepancies from the source material can arise while importing.</p>
<dl>
  <dt><a href="http://www.eldamo.org" target="_blank">Eldamo</a></dt>
  <dd><em>Eldamo</em> is perhaps the best, most comprehensive data source for Tolkien's languages to date. Maintained by Paul Strack. v. 0.8.4.1 (updated 2022-11-10).</dd>
  <dt><a href="http://folk.uib.no/hnohf/wordlists.htm" target="_blank">Quettaparma Quenyallo</a></dt>
  <dd>The best Quenya lexicon maintained by <em>Helge Fauskanger</em>, presented alongside his excellent course work material on Ardalambion.</dd>
  <dt><a href="http://folk.uib.no/hnohf" target="_blank">Parviphith</a></dt>
  <dd>Sindarin lexicon maintained by <em>Helge Fauskanger</em>.</dd>
  <dt><a href="https://github.com/Omikhleia/sindict" target="_blank">SINDICT</a></dt>
  <dd>A Sindarin and Noldorin dictionary, compiled, edited and annotated by The Sindarin Dictionary Project.</dd>
  <dt><a href="http://lambenore.free.fr/" target="_blank">Parma Eldalamberon 17 Sindarin Corpus</a></dt>
  <dd>A big thank-you to <em>David Giraudeau</em> for contributing with his compilation of Sindarin words from <em>Parma Eldalamberon</em> 17. You can find the original <a href="http://lambenore.free.fr/downloads/PE17_S.pdf" target="_blank">over here</a>.</dd>
  <dt><a href="http://www.forodrim.org/daeron/md_home.html" target="_blank">Mellonath Daeron</a></dt>
  <dd><em>Mellonath Daeron</em>'s contribution of glossaries from <em>Parma Eldalamberon</em> 18 and 19, and their continuous  feedback and encouragement.</dd>
  <dt><a href="http://www.tolkiendil.com/langues/english/i-lam_arth/compound_sindarin_names" target="_blank">Tolkiendil Compound Sindarin Names</a></dt>
  <dd><em>Tolkiendil</em> provides a consolidated list of Sindarin names examined and translated. <em>Parf Edhellen</em> is happy to house their excellent work.</dd>
</dl>
<p></p>
<p>
  We use <em>Glaemscribe</em> by <a href="https://www.jrrvf.com/glaemscrafu/english/glaemscribe.html" target="_blank">Benjamin Babut</a> for transcriptions.
  The photograph used for the main menu was taken by <a href="https://www.pexels.com/photo/evening-foggy-forest-frost-376368/" target="_blank">VisionPic .net</a>.
</p>

<a name="search"></a>
<h2>Searching</h2>
<p>You need to use the search field above in order to browse the dictionary. As you type, you will receive a list of senses and words that we believe match what you are looking for. A match can be direct and indirect. A direct match is a word that contains the characters you have entered, for an example <em>mi</em> yielding <em>mi, mir, mil</em> etcetera. An indirect match is often thematically relevant to what you are looking for, for an example <em>maple</em> yielding <em>trees, plants, olvar</em> (the latter of which is a Quenya word for “growing things with roots in the earth.”)</p> 

<p>Wildcards are supported. You can position a wildcard character <strong>*</strong> wherever you wish the dictionary to fill. Typically, a wildcard is secretly positioned at the end of what you are typing, so if you type <em>lo</em>, you are actually looking for words which begin with those letters, such as <em>long, love, low,</em> etcetera. If you want to find words that end with <em>lo</em>, you can search for <em>*lo</em>, yielding <em>hello, solo, polo,</em> etcetera. You can achieve the same result by using the dictionary’s reversed search feature, by checking the <em>Reversed</em> checkbox underneath the search field, and searching for <em>ol</em>.</p>

<p>Use wildcards to search in multiple directions, for an example <em>*en*</em> would yield <em>endeavor, envelop, generalization, sentimentality, taken,</em> ectetera.</p>

<blockquote><span class="TextIcon TextIcon--info-sign"></span> Wildcards disable thematic search when you choose a language.</blockquote>

<p>By checking the <em>Old sources</em> checkbox, the dictionary will include words from dictionaries that have not been updated for several years. These words are usually not incorrect, but they would not contain information from later linguistic publications.</p>
<a href="unverified"></a>
<h2>Unverified or debatable glosses</h2>
<p>You'll sometimes encounter the <span class="TextIcon TextIcon--asterisk"></span> symbol, usually together with a warning. These
  exist to inform you that the gloss originate from a source which might be outdated or questionable. This is unfortunately fairly common
  because linguistic material on Tolkien's languages are only sporadically made available to the community; initiatives have the time to arise
  and gradually wither between publications. Hiswelókë, which haven't been updated for years, is nonetheless still excellent, and one of the prominent
  sources to date on the Sindarin language.</p>
<p>Would it be a mistake to trust glosses from an outdated source? Probably not. I recommend you to try to find another source which corroborates
  the proposed translation.</p>

<a name="authentication"></a>
<h2>Privacy</h2>
<p>We collect only information about you when you log in, and even then, we only collect the very minimum we need to 
provide you with an unique identity. We do not and never will resell your information, and we do not share your information
with any third party. For more information, please refer to our <a href="{{ route('about.privacy') }}">Privacy Policy</a>.</p>

<h2>Cookies</h2>
<p>We use cookies to maintain session state, which is essential for knowing whether you are logged in. We also use cookies to collect anonymous 
information about how you use our service. You can read more about our use of cookies (and cookies themselves) in our 
<a href="{{ route('about.cookies') }}">Cookie Policy</a>.</p>

@endsection
