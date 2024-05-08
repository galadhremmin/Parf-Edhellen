import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<string> {
    inputSize?: 'sm' | '' | 'lg';
    languageId?: number;
    originalText: string;
}
