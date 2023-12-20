import { ComponentEventHandler } from '@root/components/Component._types';
import { IRoleManager } from '@root/security';

export interface IProps {
    isNewPost?: boolean;
    onClick: ComponentEventHandler<void>;
    roleManager?: IRoleManager;
}
