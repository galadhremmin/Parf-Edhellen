import ValidationError from '@root/connectors/ValidationError';

export interface IProps {
    error: ValidationError | Error | string | null;
}
