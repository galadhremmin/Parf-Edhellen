@php
    $word = collect(config('ed-down.words'))->random();
    $coming = array_slice((array) config('ed-down.coming', []), 0, 3);
    $handle = config('ed-down.x_handle');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  {{-- polls rather than promises: whenever the dictionary returns, the reader's tab lands on it --}}
  <meta http-equiv="refresh" content="60">
  <title>Being rebound &mdash; Parf Edhellen</title>
  <link rel="icon" type="image/png" href="/img/favicons/favicon-194x194.png">
  <style>
    @font-face {
      font-display: swap;
      font-family: 'Cormorant Garamond';
      font-style: normal;
      font-weight: 300;
      src: url('@assetpath(fonts/cormorant-garamond-latin.woff2)') format('woff2');
    }

    @font-face {
      font-display: swap;
      font-family: 'EB Garamond';
      font-style: normal;
      font-weight: 400;
      src: url('@assetpath(fonts/eb-garamond-latin.woff2)') format('woff2');
    }

    :root {
      color-scheme: light;
      --ed-bg: #F7F3EA;
      --ed-bg-elevated: #FFFDF8;
      --ed-text: #2A2620;
      --ed-text-muted: #635B4E;
      --ed-text-faint: #78705F;
      --ed-border: #E3DAC7;
      --ed-shadow: rgba(122, 104, 66, 0.12);
      --ed-gild: #C9A44C;
      --ed-gild-text: #8A6F2E;
      --ed-link: #9B4722;
    }

    @media (prefers-color-scheme: dark) {
      :root {
        color-scheme: dark;
        --ed-bg: #17161a;
        --ed-bg-elevated: #232026;
        --ed-text: #EFE3D2;
        --ed-text-muted: #a49a8b;
        --ed-text-faint: #8b8172;
        --ed-border: #3d382f;
        --ed-shadow: rgba(0, 0, 0, 0.4);
        --ed-gild-text: #C9A44C;
        --ed-link: #e2a95f;
      }
    }

    body {
      align-items: center;
      background: var(--ed-bg);
      box-sizing: border-box;
      color: var(--ed-text);
      display: flex;
      font-family: 'EB Garamond', Garamond, Georgia, 'Times New Roman', serif;
      justify-content: center;
      margin: 0;
      min-height: 100vh;
      padding: 40px 28px;
    }

    .leaf {
      max-width: 620px;
      text-align: center;
      width: 100%;
    }

    .lozenge {
      color: var(--ed-gild);
      font-size: clamp(22px, 4vw, 26px);
      line-height: 1;
      margin-bottom: clamp(26px, 5vw, 40px);
    }

    /* the gilded eyebrow over the entry, as elsewhere in the book */
    .label {
      color: var(--ed-gild-text);
      font-size: 13px;
      font-variant: small-caps;
      letter-spacing: 0.16em;
    }

    h1 {
      font-family: 'Cormorant Garamond', Garamond, Georgia, serif;
      font-size: clamp(46px, 9vw, 76px);
      font-weight: 300;
      letter-spacing: 0.01em;
      line-height: 1.05;
      margin: 12px 0 0;
      text-wrap: balance;
    }

    .cite {
      color: var(--ed-text-faint);
      font-size: 13px;
      font-variant: small-caps;
      letter-spacing: 0.12em;
      margin-top: 12px;
    }

    .gloss {
      font-size: clamp(20px, 3.4vw, 25px);
      line-height: 1.5;
      margin: 22px 0 0;
    }

    .rule {
      align-items: center;
      display: flex;
      gap: 18px;
      margin: clamp(28px, 5vw, 44px) 0 clamp(24px, 4vw, 40px);
    }

    .rule::before,
    .rule::after {
      background: var(--ed-gild);
      content: '';
      flex-grow: 1;
      height: 1px;
      opacity: 0.55;
    }

    .rule span {
      color: var(--ed-gild);
      font-size: 15px;
      line-height: 1;
    }

    .notice {
      color: var(--ed-text-muted);
      font-size: clamp(18px, 2.8vw, 21px);
      line-height: 1.65;
      margin: 0 auto;
      max-width: 520px;
    }

    .panel {
      background: var(--ed-bg-elevated);
      border: 1px solid var(--ed-border);
      border-radius: 12px;
      box-shadow: 0 2px 10px var(--ed-shadow);
      box-sizing: border-box;
      margin-top: clamp(32px, 6vw, 52px);
      padding: clamp(20px, 4vw, 30px) clamp(22px, 4vw, 36px);
    }

    .panel ul {
      display: flex;
      flex-direction: column;
      gap: 14px;
      list-style: none;
      margin: 18px 0 0;
      padding: 0;
      text-align: left;
    }

    .panel li {
      align-items: baseline;
      display: flex;
      font-size: clamp(17px, 2.6vw, 19px);
      gap: 14px;
      line-height: 1.5;
    }

    .panel li::before {
      color: var(--ed-gild);
      content: '\2727';
      font-size: 13px;
    }

    /* the machine's voice: what the page is doing, not what we hope */
    .ui {
      color: var(--ed-text-faint);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      font-size: 13px;
      line-height: 1.6;
      margin: clamp(30px, 5vw, 44px) 0 0;
    }

    .onward {
      color: var(--ed-text-muted);
      font-size: clamp(17px, 2.6vw, 19px);
      line-height: 1.6;
      margin: 12px 0 0;
    }

    .onward a {
      color: var(--ed-link);
      display: inline-block;
      padding: 6px 2px;
      text-decoration-color: color-mix(in srgb, var(--ed-link) 40%, transparent);
      text-underline-offset: 3px;
    }
  </style>
</head>
<body>
  <main class="leaf">
    <div class="lozenge">&#10022;</div>

    <div class="label">Being rebound</div>

    <h1 lang="{{ $word['language_tag'] }}">{{ $word['word'] }}</h1>

    <div class="cite">{{ $word['speech'] }} &middot; {{ $word['language'] }} &middot; [{{ $word['source'] }}]</div>

    <p class="gloss">&ldquo;{{ $word['gloss'] }}&rdquo;</p>

    <div class="rule"><span>&#10022;</span></div>

    <p class="notice">The dictionary is being rebound. Its shelves are out of order until the new leaves are sewn in.</p>

    @if (! empty($coming))
      <section class="panel">
        <div class="label">New in this binding</div>
        <ul>
          @foreach ($coming as $line)
            <li><span>{{ $line }}</span></li>
          @endforeach
        </ul>
      </section>
    @endif

    <p class="ui">This page brings itself back when we are. There is no need to reload.</p>

    @if (! empty($handle))
      <p class="onward">
        Word of it as it happens:
        <a href="https://x.com/{{ $handle }}" rel="noopener" target="_blank">{{ '@'.$handle }}</a>
      </p>
    @endif
  </main>
</body>
</html>
