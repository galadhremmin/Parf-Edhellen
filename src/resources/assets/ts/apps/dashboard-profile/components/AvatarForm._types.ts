import { ComponentEventHandler } from '@root/components/Component._types';
import { IProps as IAvatarProps } from './Avatar._types';

export interface IProps extends Partial<IAvatarProps> {
    onAvatarChange: ComponentEventHandler<File>;
}
