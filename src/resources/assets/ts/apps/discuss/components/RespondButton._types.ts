import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps {
    isNewPost?: boolean;
    onClick: ComponentEventHandler<void>;
}
