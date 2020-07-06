import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    onTick?: ComponentEventHandler<number>;
    startValue?: number;
    tick?: boolean;
    value?: number;
}