import type { ComponentEventHandler } from '@root/components/Component._types';
import type { IRoleManager } from '@root/security';

export interface IProps {
    isNewPost?: boolean;
    onClick: ComponentEventHandler<void>;
    roleManager?: IRoleManager;
}
