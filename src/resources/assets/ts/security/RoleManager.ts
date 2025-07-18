import { SecurityRole } from '../config';
import IRoleManager from './IRoleManager';
import { SecurityRoleAsString } from './RoleManager._types';

const AccountIdProperty = 'accountId';
const AccountRolesProperty = 'accountRoles';

export default class RoleManager implements IRoleManager {
    private _accountId: number = null;
    private _roles: SecurityRole[]|null = null;

    constructor(private _rootElement = document.body) {
    }

    /**
     * Gets the account ID associated with the customer.
     */
    public get accountId() {
        if (this._accountId !== null) {
            return this._accountId;
        }

        const id = this._rootElement.dataset[AccountIdProperty];
        return this._accountId = (
            id === undefined
                ? 0
                : parseInt(id, 10)
        );
    }

    /**
     * Gets the customer's active security role.
     */
    public get currentRoles() {
        if (this._roles !== null) {
            return this._roles;
        }

        const roles = (this._rootElement.dataset[AccountRolesProperty] || '') //
            .split(',') //
            .reduce((acc: SecurityRole[], role: string) => {
                const roleEnum = role as SecurityRole;
                if (roleEnum.length > 0 && roleEnum !== SecurityRole.Anonymous) {
                    acc.push(roleEnum);
                }
                return acc;
            }, []);

        // If no roles are defined, assume Anonymous. Anonymous is a pseudo-role since it doesn't actually exist in the system,
        // but it's used in the UI to indicate that the user is not authenticated.
        if (roles.length < 1) {
            roles.push(SecurityRole.Anonymous);
        }

        this._roles = roles;
        return this._roles;
    }

    public get isAdministrator() {
        return this.currentRoles.includes(SecurityRole.Root) || //
            this.currentRoles.includes(SecurityRole.Administrator);
    }

    public get isAnonymous() {
        return this.currentRoles.includes(SecurityRole.Anonymous);
    }

    public hasRole(role: SecurityRoleAsString): boolean {
        return this.currentRoles.includes(role as SecurityRole);
    }
}
