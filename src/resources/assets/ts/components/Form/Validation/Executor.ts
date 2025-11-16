import type { ReduxThunkDispatch } from '@root/_types';
import ValidationError from '@root/connectors/ValidationError';
import { setValidationErrors } from './Actions';

export const handleValidationErrors = async <T>(dispatch: ReduxThunkDispatch, p: () => Promise<T>) => {
    try {
        const d = await p();
        dispatch(setValidationErrors(null));
        return d;
    } catch (e) {
        if (e instanceof ValidationError) {
            dispatch(setValidationErrors(e));
        } else {
            throw e;
        }
    }
};
