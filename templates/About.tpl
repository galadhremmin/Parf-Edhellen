<h2>About <em>Parf Edhellen</em></h2>
<p>The collaborative dictionary dedicated to Tolkien's amazing languages. 

<a name="search"></a>
<h3>Searching</h3>
<p>Use the search field to search in the dictionaries. As you type, suggestions will appear from these, underneath the search field. Suggestions are retrieved based on your search query, and if you wish, you can use the asterisk symbol (*) where you're uncertain about the spelling, or when you simply wish to match everything. A single asterisk isn't a valid search query however!</p>

<p>Special characters are normalized into their ASCII equivalents. This means that if you search for <i>mir</i>, your query will also match <i><a href="#m%C3%AEr">mîr</a></i>, <i><a href="#m%C3%ADr%C3%AB">mírë</a></i>, <i><a href="#miril">miril</a></i> and so forth. </p>

<p>As you type, you'll notice that the list of suggestions can become really extensive. Therefore, there's a cap on how many suggestions we provide for you, depending on the preciseness of your search query. More exact queries (= multiple search terms) are permitted to yield more suggestions. This feature also exists to improve performance for everyone.</p>

<p>The preciseness of your query is calculated according to the follow equation: 
<br /><u><em>the length of the search term (without white space)</em></u> &times; 200.</p>

<a name="reversed"></a>
<h3>Reversed search</h3>
<p>If you tick this checkbox, your search query will be reversed before being passed to the search engine. This is useful when you're looking for words with a specific word ending,  perhaps with the intention to find fitting rhymes for your poetry.</p>

<a href="unverified"></a>
<h3>Unverified or debatable glosses</h3>
<p>Sometimes, you'll encounter the <span class="glyphicon glyphicon-question-sign"></span> symbol, accompanied with a warning. These exist to inform you that the gloss might have been imported from a source which might not have been updated in quite some time. This is actually quite common, as publications from the Tolkien Estate come infrequently, often with years apart. Dictionaries like Hiswelókë haven't been updated for years, but they're still excellent.</p>
<p>Are these words incorrect? Probably not. Can you use them? Yes, but try to find another source which corroborates the proposed translation. In any case, all words which come from an outdated, unverified or debatable source have been pushed to the bottom of the page.</p>

<a name="authentication"></a>
<h3>Authentication</h3>
<p>We don't maintain our own database with user credentials. Instead, we prefer to use federated login services through 
<a href="http://openid.net/" target="_blank">OpenID</a>. By logging in using OpenID, no personal information is compromised, as you only prove that you're you by logging in to a common social network. So if you log into Gmail, Gmail confirms to us that you successfully logged in, and provides us with an unique key which we can use to tie user data (such as your preferred nickname) to you.</p>

<a name="contributing"></a>
<h3>Contributing</h3>
<p>Note: we're still working on a new log in experience. So sorry for the inconvenience!</p>

<p>We want everyone to contribute. All sorts of contributions are encouraged, but if you intend to publish your own words onto our site, please remember to <i>always</i> include references to published sources. We reserve the right to emend and delete content we find inappropriate.</p>

<p>There are two ways to contribute: by adding words and by adding glosses. Each word might have multiple
glosses in many languages.</p>

<p>Syntax:<br />
<span class="span-column">[[maen]]</span> <a href="index.php#maen">maen</a><br /> 
<span class="span-column">_mae_</span> <em>mae</em><br />
<span class="span-column">~minno~</span> <u>minno</u><br />  
<span class="span-column">`idhron`</span> <strong>idhron</strong><br /> 
<span class="span-column">&gt;&gt;</span> <img src="img/hand.png" alt="" border="0" /></p>

<a name="wordlist"></a>
<h3>Credits &amp; Sources</h3>
<p>The following excellent dictionaries have been successfully imported:</p>
<dl>
  <dt><a href="http://www.eldamo.org" target="_blank">Eldamo</a></dt>
  <dd><em>Eldamo</em> is perhaps the best, most comprehensive, authoritative source for Tolkien's languages to date.</dd>
  <dt><a href="http://folk.uib.no/hnohf/wordlists.htm" target="_blank">Quettaparma Quenyallo</a></dt>
  <dd>The best Quenya lexicon maintained by <em>Helge Fauskanger</em>, presented alongside his excellent course work material on Ardalambion.</dd>
  <dt><a href="http://folk.uib.no/hnohf" target="_blank">Parviphith</a></dt>
  <dd>Sindarin lexicon maintained by <em>Helge Fauskanger</em>.</dd>
  <dt><a href="http://www.jrrvf.com/hisweloke/sindar/index.html" target="_blank">Hiswelókë's Sindarin Dictionary</a></dt>
  <dd>A community dictionary maintained by the <em>SinDict community</em> that has unfortunately stagnated, but is still very good.</dd>
  <dt><a href="http://lambenore.free.fr/" target="_blank">Parma Eldalamberon 17 Sindarin Corpus</a></dt>
  <dd>A big thank-you to <em>David Giraudeau</em> for contributing with his compilation of Sindarin words from <em>Parma Eldalamberon</em> 17. You can find the original <a href="http://lambenore.free.fr/downloads/PE17_S.pdf" target="_blank">over here</a>.</dd>
  <dt><a href="http://www.forodrim.org/daeron/md_home.html" target="_blank">Mellonath Daeron</a></dt>
  <dd><em>Mellonath Daeron</em>'s contribution of glossaries from <em>Parma Eldalamberon</em> 18 and 19, and their continuous  feedback and encouragement.</dd>
  <dt><a href="http://www.tolkiendil.com/langues/english/i-lam_arth/compound_sindarin_names" target="_blank">Tolkiendil Compound Sindarin Names</a></dt>
  <dd><em>Tolkiendil</em> provides a consolidated list of Sindarin names examined and translated. <em>Parf Edhellen</em> is happy to house their excellent work.</dd>
</dl>
<p>Thank you for your excellent work! It is the quintessence of Parf Edhellen's success!</p>

<a name="tengwar"></a>
<h3>Tengwar <span class="tengwar">1Rx#6</span></h3>
<p>It's possible to write tengwar on <em>elfdict</em> by use of a technology called &ldquo;CSS3 webfonts&rdquo;. The tengwar that you&rsquo;ll find scattered about this website have been either added by a contributor <em>or</em>, more commonly, automatically transcribed for Sindarin and Noldorin. The open-source library <a href="https://github.com/kriskowal/tengwarjs" target="_blank">tengwarjs</a> by Kris Kowal enables us to transcribe at runtime, which means that generated the tengwar markup is <em>not</em> associated with the source. The quality of the transcription might vary and all transcribed words are marked with a link to this note.</p>

<!--<h3>Support the <em>Parf Edhellen</em>!</h3>
<p>Donations are more than welcome! Every contribution helps me to maintain the development and hosting of <em>Parf Edhellen</em>.</p>-->
