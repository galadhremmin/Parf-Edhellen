export const ApiPath = '/api/v2';
export const ApiExceptionCollectorMethod = 'utility/error';
export const ApiValidationFailedStatusCode = 422;

export const ApplicationGlobalPrefix = 'ed';
export const LocalStorageLanguages = 'ed.languages';

export const GlobalEventLoadGlossary = 'ednavigate';
export const GlobalEventLoadReference = 'edref';

export const DefaultGlaemscribeCharacterSet = 'tengwar_ds_annatar';

export enum SecurityRole {
    Anonymous = 'ed-anonymous',
    User = 'ed-user',
    Administrator = 'ed-admin',
}
