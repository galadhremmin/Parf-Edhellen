@extends('_layouts.default')

@section('title', 'About')
@section('body')

<h1>About <span class="tengwar" title="Parf Edhellen">q7Er 4$j$¸5$</span></h1>
<p>The collaborative dictionary dedicated to Tolkien's amazing languages. 

<a name="search"></a>
<h2>Searching</h2>
<p>Use the search field above to browse the dictionary. As you type, the dictionary will present suggestions underneath based on what you type.
  You can use the asterisk symbol (*) to match everything or individual letters, which can be useful if you're unsure about spelling. A single
  asterisk won't yield the entire library, however, because the precision of your query is considered too low.</p>

<p>Words with special letters, like letters with umlauts and accents, are matched against their their ASCII letters. This means that if you
  search for <i>mîr</i>, the dictionary will suggest <i><a href="/w/mir">mîr</a></i>, <i><a href="/w/mire">mírë</a></i>,
  <i><a href="/w/miril">miril</a></i> etc.</p>

<p>As you type, you'll notice that the list of suggestions beneath can become really long, but there is a cap on how many suggestions you can
  view at one time, depending on the preciseness of your search query. More exact queries (= more letters) are permitted to yield a longer
  list of suggestions. This feature also exists to improve performance for everyone.</p>

<p>The preciseness of your query is calculated according to the follow equation: 
<br /><u><em>the length of the search term (without white space)</em></u> &times; 200.</p>

<a name="reversed"></a>
<h2>Reversed search</h2>
<p>If you tick the checkbox &quot;reversed search,&quot; <em>Parf Edhellen</em> will match your query in reverse. This is useful if you're
  looking for words with a specific word ending, perhaps with the intention to find fitting rhymes for your poetry.</p>

<a href="unverified"></a>
<h2>Unverified or debatable glosses</h2>
<p>You'll sometimes encounter the <span class="glyphicon glyphicon-asterisk"></span> symbol, usually together with a warning. These
  exist to inform you that the gloss originate from a source which might be outdated or questionable. This is unfortunately fairly common
  because linguistic material on Tolkien's languages are only sporadically made available to the community; initiatives have the time to arise
  and wither between publications. Hiswelókë, which haven't been updated for years, is nonetheless still excellent, and one of the prominent
  sources to date on the Sindarin language.</p>
<p>Would it be a mistake to trust glosses from an outdated source? Probably not. I recommend you to try to find another source which corroborates
  the proposed translation.</p>

<a name="authentication"></a>
<h2>Logging in</h2>
<p>We don't manage your credentials; we trust Facebook, Google, Twitter to do that for us. So when you log in, these services simply vouch for you,
  and give us a token which uniquely identifies you in their systems. Therefore you share no personal information with <em>Parf Edhellen</em>
  (apart from your e-mail address) and thus cannot lose your information in the event of a breach.</p>
<p>Logging in is simple: choose which community which would vouch for you, and log in there. Once you've logged in, and given <em>Parf Edhellen</em>
  permission to access basic information about you, you'll be sent back to here, where you'll be asked to choose a nickname for yourself.</p>
<p>To identify yourself in the future, <em>Parf Edhellen</em> saves an unique token associated with your e-mail address.</p>

<p>Syntax:<br />
<span class="span-column">[[maen]]</span> <a href="/w/maen">maen</a><br />
<span class="span-column">_mae_</span> <em>mae</em><br />
<span class="span-column">~minno~</span> <u>minno</u><br />  
<span class="span-column">`idhron`</span> <strong>idhron</strong><br /> 
<span class="span-column">&gt;&gt;</span> <img src="img/hand.png" alt="" border="0" /></p>

<a name="wordlist"></a>
<h2>Credits &amp; Sources</h2>
<p><em>Parf Edhellen</em> imported its definition from the excellent dictionaries listed below.
  Please note that discrepancies from the source material can arise while importing.</p>
<dl>
  <dt><a href="http://www.eldamo.org" target="_blank">Eldamo</a></dt>
  <dd><em>Eldamo</em> is perhaps the best, most comprehensive data source for Tolkien's languages to date. Maintained by Paul Strack. v. 0.5.6 (updated 2017-06-30).</dd>
  <dt><a href="http://folk.uib.no/hnohf/wordlists.htm" target="_blank">Quettaparma Quenyallo</a></dt>
  <dd>The best Quenya lexicon maintained by <em>Helge Fauskanger</em>, presented alongside his excellent course work material on Ardalambion.</dd>
  <dt><a href="http://folk.uib.no/hnohf" target="_blank">Parviphith</a></dt>
  <dd>Sindarin lexicon maintained by <em>Helge Fauskanger</em>.</dd>
  <dt><a href="http://www.jrrvf.com/hisweloke/sindar/index.html" target="_blank">Hiswelókë's Sindarin Dictionary</a></dt>
  <dd>A dictionary project initiated by <em>Didier Willis</em> and maintained by the SinDict community, ancient, yet still legendary.</dd>
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
  <a href="https://pixabay.com/en/users/Mysticsartdesign-322497/">Mystic Art Design</a> photoshopped the jumbotron's background, and the photograph of the 
  roots used for the background was taken by <a href="https://pixabay.com/en/users/tpsdave-12019/" target="_blank">tpsdave</a>.
</p>

@endsection
