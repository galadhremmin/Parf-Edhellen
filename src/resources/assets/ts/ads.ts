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
      dataset: {
        ...dataset,
        adSlot: '3964210950',
      },
    },
    frontpage: {
      props,
      dataset: {
        ...dataset,
        adSlot: '6826878711',
      },
    },
    phrases: {
      props,
      dataset: {
        ...dataset,
        adSlot: '3237620511',
      },
    },
    glossary: {
      props,
      dataset: {
        ...dataset,
        adFormat: 'auto',
        adSlot: '7637931221',
        fullWidthResponsive: 'true',
      },
    },
    forum: {
      props,
      dataset: {
        ...dataset,
        adFormat: 'auto',
        adSlot: '9202116727',
        fullWidthResponsive: 'true',
      },
    },
    sage: {
      props,
      dataset: {
        ...dataset,
        adSlot: '3767254384',
      },
    },
    _mount: () => {
      // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
      ((window[googleAdsKey] || []) as any).push({});
    },
  };

  // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
  window[configKey] = config;
})();
