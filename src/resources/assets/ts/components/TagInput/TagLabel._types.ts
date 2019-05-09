import { ComponentEventHandler } from '../Component._types';

export interface IProps {
    tag: string;
    onDelete: ComponentEventHandler<string>;
}
