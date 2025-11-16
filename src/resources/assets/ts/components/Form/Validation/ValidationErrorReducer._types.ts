import type { IReduxAction } from '@root/_types';
import ValidationError from '@root/connectors/ValidationError';

export interface IAction extends IReduxAction {
    errors: ValidationError;
}
