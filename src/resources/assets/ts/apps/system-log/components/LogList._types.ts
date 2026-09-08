import type { ILogApi } from '@root/connectors/backend/ILogApi';
import type IRoleManager from '@root/security/IRoleManager';

export interface IProps {
    logApi: ILogApi;
    category?: string;
    accountId?: number;
    ip?: string;
    week?: string;
    year?: number;
    weekNumber?: number;
    roleManager?: IRoleManager;
    onCategoryDeleted?: () => void;
}
