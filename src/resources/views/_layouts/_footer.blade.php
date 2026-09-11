{{-- The site's closing furniture. Extracted from default.blade.php so it can be
     styled from tokens: it used Bootstrap's bg-dark, which never follows a
     theme change and so stayed black on the light theme. --}}
<footer class="ed-site-footer" data-ad-region="no-ads">
  <div class="container">
    <div class="ed-site-footer__inner">
      <section class="ed-site-footer__section">
        <h3 class="ed-site-footer__title">{{ config('ed.title') }}</h3>
        <nav>
          <ul class="ed-site-footer__nav">
            <li><a href="{{ route('login') }}">Sign in</a></li>
            <li><a href="{{ route('about') }}">About the website</a></li>
            <li><a href="{{ route('about.cookies') }}">Cookie policy</a></li>
            <li><a href="{{ route('about.privacy') }}">Privacy policy</a></li>
          </ul>
        </nav>
      </section>
      <section class="ed-site-footer__section ed-site-footer__disclaimer">
        Black Speech, Nandorin, Noldorin, Quendya, Quenya, Sindarin and Telerin are languages
        conceived by Tolkien and they do not belong to us; we neither can nor do claim affiliation
        with <a href="http://www.middleearth.com/" target="_blank" rel="noopener">Middle-earth Enterprises</a>
        nor <a href="http://www.tolkienestate.com/" target="_blank" rel="noopener">Tolkien Estate</a>.
      </section>
    </div>
  </div>
</footer>
