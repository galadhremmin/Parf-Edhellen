export const ApiPath = '/api/v2';
export const ApiExceptionCollectorMethod = 'utility/error';
export const ApiValidationFailedStatusCode = 422;

export const ApplicationGlobalPrefix = 'ed';
export const LocalStorageLanguages = 'ed.languages';

export const GlobalEventLoadGlossary = 'ednavigate';
export const GlobalEventLoadEntity = 'ednavigate-entity';
export const GlobalEventLoadReference = 'edref';

export const GlobalAdsConfigurationName = 'ed.ads.config';

export const DefaultGlaemscribeCharacterSet = 'tengwar_guni_annatar';
export const GlaemscribeModeMappings: { [mode: string]: string } = {
    'blackspeech': 'blackspeech-tengwar-general_use',
    'quenya': 'quenya-tengwar-classical',
    'sindarin': 'sindarin-tengwar-general_use',
    'sindarin-beleriand': 'sindarin-tengwar-beleriand',
    'telerin': 'telerin-tengwar-glaemscrafu',
    'westron': 'westron-tengwar-glaemscrafu',
};

// See SearchKeyword.php
export const SearchResultGroups = {
    '0': 'Unassigned',
    '1': 'Glossary',
    '2': 'Phrases',
    '3': 'Discuss',
};
export const SearchResultGlossaryGroup = SearchResultGroups[1];

export const LearnMoreMarkdownUrl = 'https://en.wikipedia.org/wiki/Markdown';
export const LearnMoreWebFeedUrl = 'https://en.wikipedia.org/wiki/Web_feed';

export const AnonymousAvatarPath = '/img/anonymous-profile-picture.png';

export enum SecurityRole {
    Anonymous = 'ed-anonymous',
    User = 'ed-user',
    Administrator = 'ed-admin',
}

export const CommonPaths = {
    contributions: {
        gloss: '/dashboard/contribution/create/gloss',
        sentence: '/dashboard/contribution/create/sentence',
    },
    dashboard: '/dashboard',
};
