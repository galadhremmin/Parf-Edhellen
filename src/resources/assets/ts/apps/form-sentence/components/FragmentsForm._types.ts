import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    onChange: ComponentEventHandler<string>;
    text: string;
}
