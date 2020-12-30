(() => {
  const minHeight = '140px';
  const maxHeight = 'auto';
  const props = {
    className: 'adsbygoogle',
    style: {
      display: 'block',
      minHeight,
      maxHeight,
    },
  };
  const dataset = {
    adClient: 'ca-pub-8268364504414566',
    fullWidthResponsive: true,
  };
  const googleAdsKey: any = 'adsbygoogle';
  const configKey: any = 'ed.ads.config';
  const config: any = {
    flashcards: {
      props,
      dataset: Object.assign({
        adSlot: '3964210950',
      }, dataset),
    },
    frontpage: {
      props,
      dataset: Object.assign({
        adSlot: '6826878711',
      }, dataset),
    },
    phrases: {
      props,
      dataset: Object.assign({
        adSlot: '3237620511',
      }, dataset),
    },
    glossary: {
      props,
      dataset: Object.assign({
        adFormat: 'auto',
        adSlot: '7637931221',
        fullWidthResponsive: 'true',
      }, dataset),
    },
    forum: {
      props,
      dataset: Object.assign({
        adFormat: 'auto',
        adSlot: '9202116727',
        fullWidthResponsive: 'true',
      }, dataset),
    },
    sage: {
      props,
      dataset: Object.assign({
        adSlot: '3767254384',
      }, dataset),
    },
    _mount: () => {
      ((window[googleAdsKey] || []) as any).push({});
    },
  };

  window[configKey] = config;
})();
