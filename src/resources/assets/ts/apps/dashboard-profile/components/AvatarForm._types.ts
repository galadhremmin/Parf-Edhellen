import { IProps as IAvatarProps } from '@root/components/Avatar._types';
import { ComponentEventHandler } from '@root/components/Component._types';

export interface IProps extends Partial<IAvatarProps> {
    onAvatarChange: ComponentEventHandler<File>;
}
