<h2>About <em>Parf Edhellen</em></h2>
<p>The collaborative dictionary dedicated to the the linguistic world of Tolkien's. Presently in <em>alpha</em>-stage.</p>

<a name="search"></a>
<a name="search"></a>
<h3>Searching</h3>
<p>Use the search-box to browse the dictionaries contents. As you type, suggestions are made continously 
based on your enquiry. It is possible to use wildcards to further enhance the quality of these suggestions.</p>

<p>Special characters aren't normalized, which means that you would have to explicitly search for <i>mîr</i> 
or <i>aníra</i> etc. This is a known issue that will be fixed as soon as a viable solution comes to mind.
For the time being, you can use wildcards instead of special characters, if you are uncertain which to use:
<em>m*r</em> and <em>an*ra</em>.</p>

<p>A result cap is applied depending on the preciseness of your query. More exact queries are believed to yield more granular results, consequently resulting in a wider more generous result cap. This feature is chiefly in place to limit excessive queries that might impede performance.</p>

<p>The preciseness of your query is calculated according to the follow equation: 
<br /><u><em>the length of the input string without modifiers and spacing</em></u> &times; 100.</p>

<a name="authentication"></a>
<h3>Authentication</h3>
<p><em>Parf Edhellen</em> does not maintain its own authentication database. Instead, it uses 
<a href="http://openid.net/" target="_blank">OpenID</a> for account management. By logging in using
OpenID, no personal information is compromised and your account names as well as passwords are maintained
independently by each authentication provider. What this site however maintains is an unique ID that identifies
your authenticated account.</p>

<p>The first time you log in, you will be asked to specify an alias. This alias will be your public face and
used for revision management.</p>

<a name="contributing"></a>
<h3>Contributing</h3>
<p>The gist of this website is the strength of aggregated expertise. Your contributions are encouraged,
but please remember to <i>always</i> publish references backing up your claims. Moderators reserve the rights 
to emend and delete content.</p>

<p>There are two ways to contribute: by adding words and by adding glosses. Each word might have multiple
glosses in many languages.</p>

<p>Syntax:<br />
<span class="span-column">[[maen]]</span> <a href="index.php#maen">maen</a><br /> 
<span class="span-column">_mae_</span> <em>mae</em><br /> 
<span class="span-column">`idhron`</span> <strong>idhron</strong><br /> 
<span class="span-column">&gt;&gt;</span> <img src="img/hand.png" alt="" border="0" /></p>

<a name="wordlist"></a>
<h3>Word lists</h3>
<p>The following excellent word lists have been successfully imported.</p>
<dl>
  <dt><a href="http://folk.uib.no/hnohf/wordlists.htm" target="_blank">Quettaparma Quenyallo</a></dt>
  <dd>The best Quenya lexicon maintained by <em>Helge Fauskanger</em>, presented alongside his <em>excellent</em> course work material on Ardalambion.</dd>
  <dt><a href="http://www.jrrvf.com/hisweloke/sindar/index.html" target="_blank">Hiswelókë's Sindarin Dictionary</a></dt>
  <dd>A community dictionary maintained by the <em>SinDict community</em> that has unfortunately stagnated, but is still very good.</dd>
</dl>
<p>Thank you for your excellent work making these word lists available to the public!</p>

<a name="tengwar"></a>
<h3>Tengwar <span class="tengwar">1Rx#6</span></h3>
<p>It's possible to write tengwar on <em>elfdict</em> by use of a technology called &ldquo;CSS3 webfonts&rdquo;. The tengwar that you&rsquo;ll find scattered about this website have been either added by a contributor <em>or</em>, more commonly, automatically transcribed for Sindarin and Noldorin. The open-source library <a href="https://github.com/kriskowal/tengwarjs" target="_blank">tengwarjs</a> by Kris Kowal enables us to transcribe at runtime, which means that generated the tengwar markup is <em>not</em> associated with the source. The quality of the transcription might vary and all transcribed words are marked with a link to this note.</p>


<!--<h3>Support the <em>Parf Edhellen</em>!</h3>
<p>Donations are more than welcome! Every contribution helps me to maintain the development and hosting of <em>Parf Edhellen</em>.</p>-->