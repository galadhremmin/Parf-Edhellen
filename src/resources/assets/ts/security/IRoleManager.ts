import { SecurityRole } from '../config';
import type { SecurityRoleAsString } from './RoleManager._types';

export default interface IRoleManager {
    readonly accountId: number;
    readonly currentRoles: SecurityRole[];
    readonly isAdministrator: boolean;
    readonly isAnonymous: boolean;
    hasRole(role: SecurityRoleAsString): boolean;
}
