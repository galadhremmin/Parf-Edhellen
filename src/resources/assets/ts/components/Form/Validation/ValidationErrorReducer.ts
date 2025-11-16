import ValidationError from '@root/connectors/ValidationError';

import { Actions } from './Actions';
import type { IAction } from './ValidationErrorReducer._types';

export const ValidationErrorReducer = (state: ValidationError = null, action: IAction) => {
    switch (action.type) {
        case Actions.SetValidationErrors:
            if (action.errors instanceof ValidationError) {
                return action.errors;
            }

            return null;
        default:
            return state;
    }
};
