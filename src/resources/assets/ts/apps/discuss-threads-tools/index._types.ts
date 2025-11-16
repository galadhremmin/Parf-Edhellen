import type IDiscussApi from '@root/connectors/backend/IDiscussApi';
import type { IRoleManager } from '@root/security';

export interface IProps {
    apiConnector: IDiscussApi;
    groupId: number;
    groupName: string;
    roleManager: IRoleManager;
}
