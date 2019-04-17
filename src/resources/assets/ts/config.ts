export const ApiPath = '/api/v2';
export const ApiExceptionCollectorMethod = 'utility/error';
export const ApiValidationFailedStatusCode = 422;

export const ApplicationGlobalPrefix = 'ed';
export const LocalStorageLanguages = 'ed.languages';

export const GlobalEventLoadGlossary = 'ednavigate';
export const GlobalEventLoadReference = 'edref';

export const DefaultGlaemscribeCharacterSet = 'tengwar_ds_annatar';

export const LearnMoreMarkdownUrl = 'https://en.wikipedia.org/wiki/Markdown';
export const LearnMoreWebFeedUrl = 'https://en.wikipedia.org/wiki/Web_feed';

export enum SecurityRole {
    Anonymous = 'ed-anonymous',
    User = 'ed-user',
    Administrator = 'ed-admin',
}
