export const ApiPath = '/api/v3';
export const ApiExceptionCollectorMethod = 'utility/error';
export const ApiValidationFailedStatusCode = 422;

export const AvatarMaximiumFileSize = Math.pow(1024,2)/2;
export const AvatarMaximumImageWidthInPixels = 160;

export const ApplicationGlobalPrefix = 'ed';
export const LocalStorageLanguages = 'ed.languages';

export const GlobalEventLoadGlossary = 'ednavigate';
export const GlobalEventLoadEntity = 'ednavigate-entity';
export const GlobalEventLoadReference = 'edref';
export const GlobalEventErrorLogger = 'ederror';

export const GlobalAdsConfigurationName = 'ed.ads.config';

export const ApiTimeoutInMilliseconds = 15000;

export const DefaultGlaemscribeCharacterSet = 'tengwar_guni_annatar';
export const GlaemscribeModeMappings: { [mode: string]: string } = {
    'blackspeech': 'blackspeech-tengwar-general_use',
    'quenya': 'quenya-tengwar-classical',
    'sindarin': 'sindarin-tengwar-general_use',
    'sindarin-beleriand': 'sindarin-tengwar-beleriand',
    'telerin': 'telerin-tengwar-glaemscrafu',
    'westron': 'westron-tengwar-glaemscrafu',
};

export const SearchResultGlossaryGroupId = 1;

export const LearnMoreMarkdownUrl = 'https://en.wikipedia.org/wiki/Markdown';
export const LearnMoreWebFeedUrl = 'https://en.wikipedia.org/wiki/Web_feed';

export const AnonymousAvatarPath = '/img/anonymous-profile-picture.png';

export const enum SecurityRole {
    Anonymous = 'Anonymous',
    Administrator = 'Administrators',
    User = 'Users',
    Root = 'Root',
    Discuss = 'Discuss',
}

export const CommonPaths = {
    contributions: {
        lexicalEntry: '/contribute/contribution/create/lexical_entry',
        sentence: '/contribute/contribution/create/sentence',
    },
    privacyPolicy: '/about/privacy',
    cookiePolicy: '/about/cookies',
};

export const CacheLengthMinutes = {
    languages: 60 * 24,
};

export const TimezonesWithEuConsent = [
    'Europe/Vienna',
    'Europe/Brussels',
    'Europe/Sofia',
    'Europe/Zagreb',
    'Asia/Famagusta',
    'Asia/Nicosia',
    'Europe/Prague',
    'Europe/Copenhagen',
    'Europe/Tallinn',
    'Europe/Helsinki',
    'Europe/Paris',
    'Europe/Berlin',
    'Europe/Busingen',
    'Europe/Athens',
    'Europe/Budapest',
    'Europe/Dublin',
    'Europe/Rome',
    'Europe/Riga',
    'Europe/Vilnius',
    'Europe/Luxembourg',
    'Europe/Malta',
    'Europe/Amsterdam',
    'Europe/Warsaw',
    'Atlantic/Azores',
    'Atlantic/Madeira',
    'Europe/Lisbon',
    'Europe/Bucharest',
    'Europe/Bratislava',
    'Europe/Ljubljana',
    'Africa/Ceuta',
    'Atlantic/Canary',
    'Europe/Madrid',
    'Europe/Stockholm',
    'America/Los_Angeles', // California
];
export const AdvertisingUseCaseScriptName = 'advertising';
export const CookieUseCases = [
    {
        domain: 'Essential',
        description: 'Essential cookies for basic functionality on the website, including protecting against cross-site request forgery (CSRF). These cookies are required for the website to function and we will assume implicit consent.',
        readonly: true,
        scriptName: 'required',
    },
    {
        domain: 'Analytics',
        description: 'Helps us understand how our guests interact with the website so we can find issues and improve the user experience in the future.',
        readonly: false,
        readMore: 'https://support.google.com/analytics/answer/11397207?hl=en',
        scriptName: 'analytics',
    },
    {
        domain: 'Advertising',
        description: 'Third-party cookies that personalizes ads and improves their relevancy. The purpose is to advertise things you might be interested in.',
        readonly: false,
        readMore: 'https://business.safety.google/adscookies/',
        scriptName: AdvertisingUseCaseScriptName,
    },
];
export const EuConsentCookieName = 'ed-euconsent-v2';
export const EuConsentCookieSelection = 'ed-euconsent-usecases-v2';
export const EuConsentGivenCookieValue = 'true';
export const EuConsentExemptionPaths = [CommonPaths.cookiePolicy, CommonPaths.privacyPolicy];
