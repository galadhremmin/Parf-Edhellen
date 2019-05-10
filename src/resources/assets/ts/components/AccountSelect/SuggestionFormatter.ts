import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

export const getSuggestionValue = (suggestion: IAccountSuggestion) => `${suggestion.nickname}`;
