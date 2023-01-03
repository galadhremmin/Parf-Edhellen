import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    showOverrideOption?: boolean;
    onOverrideOptionTriggered?: ComponentEventHandler<void>;
}
