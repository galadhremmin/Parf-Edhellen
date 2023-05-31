@extends('_layouts.default')

@section('title', 'About')
@section('body')

<h1>About <span class="tengwar" title="About Parf Edhellen">  </span></h1>
<p>Welcome to the <em>Parf Edhellen</em>, a collaborative dictionary dedicated to Tolkien’s amazing languages. The dictionary contains all Elvish languages, including Telerin and Quenya, with words imported from a variety of sources. The researchers of Tolkien’s languages have carefully compiled these words. Parf Edhellen means “Elvish Book” in Sindarin, the noble language of elves and men.</p>
<p>
  Tolkien was an amazing linguist who devised beautiful languages for his legendarium, and his Elvish languages are some of the most fully-realized fictional languages ever created. They have their own grammar, vocabulary, and syntax, and are spoken by characters in his books.
  You'll find information about all these aspects of his languages here with references to the source material. You'll also find so called reconstructions in the dictionary, words created by the linguistic community according using the same rules as Tolkien.
</p>

<p><em>Parf Edhellen</em> is open source, and actively developed and maintained by Leonard &ldquo;Aldaleon&rdquo;, 
a Tolkien-fan.</p>

<h2>What is Elvish?</h2>

<p>Elvish, or the Elvish languages of Middle-earth, were constructed by J.R.R. Tolkien for the Elves of Middle-earth as they developed as a society throughout the ages. Although Quenya and Sindarin are the most famous and the most developed of the languages that Tolkien invented for his Secondary World, they are by no means the only ones.</p>

<p>Quenya is one of the two most prominent Elvish languages in Tolkien’s legendarium. It is an archaic language that was spoken by the High Elves of Valinor. Sindarin is another prominent Elvish language in Tolkien’s legendarium. It is a language that was spoken by the Grey Elves and later by the Elves of Middle-earth.</p>
<p>In addition to Quenya and Sindarin, Tolkien created several other languages for his legendarium. These include Telerin, Noldorin, Silvan, Avarin, Vanyarin, and Valarin. Telerin is the language of the Teleri, a group of Elves who stayed behind during the Great Journey to Aman. Noldorin is a precursor to Sindarin. Silvan is a language that was spoken by the Wood-elves of Middle Earth. Avarin is a language that was spoken by the Avari, a group of Elves who refused to undertake the Great Journey to Aman. Vanyarin is an archaic language that was spoken by the Vanyar. Valarin is the language of the Valar, the angelic beings who created the world of Arda.</p>
<p>You will find all these languages in the dictionary! If you are studying these languages and perhaps intend to write some prose of your own, please remember never to mix words from different languages: it would be like mixing English with German!</p>

<h2>Can you write Elvish?</h2>
<p>Yes, but it is not as straight forward as you might think. Tolkien developed many writing systems, including Cirth, Sarati and Tengwar. Tengwar was used for the One Ring and has become a popular writing system for wedding bands.</p>

<a name="search"></a>
<h2>Browsing the dictionary</h2>
<p>Use the search field above to browse the dictionary. As you type, you will be presented with a list of words that match your search term. A match can be direct and indirect: a direct match is a word that contains the characters you have entered, for an example <em>mi</em> yielding <em>mi, mir, mil</em> etcetera; an indirect match is thematically relevant to what you are looking for, for an example <em>maple</em> yielding <em>trees, plants, olvar</em> (the latter of which is a Quenya word for “growing things with roots in the earth.”)</p> 

<p>Wildcard, or the asterisk, search terms are supported. You can position a wildcard character <strong>*</strong> wherever you wish and the dictionary will fill in the rest. A wildcard is usually inserted at the end of what you are typing, so if you type <em>lo</em>, you will find words that begin with those letters, such as <em>long, love, low,</em> etcetera. If you want to find words that end with <em>lo</em>, you can search for <em>*lo</em>, yielding <em>hello, solo, polo,</em> etcetera. You can achieve the same result by using the dictionary’s reversed search feature, by checking the <em>Reversed</em> checkbox underneath the search field, and searching for <em>ol</em>.</p>

<p>Use wildcards to search in multiple directions, for an example <em>*en*</em> would yield <em>endeavor, envelop, generalization, sentimentality, taken,</em> ectetera.</p>

<blockquote><span class="TextIcon TextIcon--info-sign"></span> Wildcards disable thematic search when you choose a language.</blockquote>

<p>By checking the <em>Old sources</em> checkbox, the dictionary will include words from dictionaries that have not been updated for several years. These words are usually not incorrect, but they can lack information from later linguistic publications.</p>

<h2>Terminology &amp; Notation</h2>
<p>
  You will find that some words come with symbols as a prefix or suffix. These symbols have meaning and are designed to give you information into how they were derived or discovered:
</p>
<ul>
  <li>* &mdash; reconstructed gloss or a translation into English that better represents its intended meaning.</li>
  <li>√ &mdash; primitive base or root from which words were created.</li>
  <li># &mdash; word usually derived from an element of an attested compound.</li>
  <li>† &mdash; archaic or poetic word.</li>
  <li><s>word</s> &mdash; rejected by Tolkien at some point, usually later in the conceptual development.</li>
  <li>✶ &mdash; Eldamo's notation for a primitive word form, usually (though not always) Primitive Elvish.</li>
  <li>ᴺ &mdash; Eldamo's notation for a neologism, newly created words that are extrapolations from Tolkien’s writings. There are vigorous debates on which techniques are legitimate methods for creating neologisms (and even what should or shouldn’t be considered a neologism).</li>
  <li>‽ &mdash; Eldamo's notation for where Tolkien himself wrote a “?” in the source material.</li>
</ul>

@if ($languages->count() > 0)
<p>
  Most languages have a shorter version of their name. This is mostly used to point out cognates in other languages than the one you are browsing:
</p>
<ul>
  @foreach ($languages as $language)
  <li>{{ strtoupper($language->short_name) }}. &mdash; {{ $language->name }}</li>
  @endforeach
</ul>
@endif 

<a href="unverified"></a>
<h2>Reconstructed or debatable glosses</h2>
<p>You will sometimes encounter the <span class="TextIcon TextIcon--asterisk"></span> symbol, usually together with a warning. These
  exist to inform you that the gloss originate from a source which might be outdated or questionable. This is unfortunately fairly common
  because linguistic material on Tolkien's languages are only sporadically made available to the community; initiatives have the time to arise
  and gradually wither between publications. Hiswelókë, which haven't been updated for years, is nonetheless still excellent, and one of the prominent
  sources to date on the Sindarin language.</p>
<p>Would it be a mistake to trust glosses from an outdated source? Probably not. I recommend you to try to find another source which corroborates
  the proposed translation.</p>

<a name="wordlist"></a>
<h2>Credits &amp; Sources</h2>
<p><em>Parf Edhellen</em> imported its definition from the excellent dictionaries listed below.
  Discrepancies from the source material can arise while importing, though the import process has improved significantly over the years.
</p>
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
