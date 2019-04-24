import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import { RoleManager } from '@root/security';

export interface IProps {
    apiConnector: DiscussApiConnector;
    groupId: number;
    groupName: string;
    roleManager: RoleManager;
}
