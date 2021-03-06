import { SecurityRole } from '../config';
import IRoleManager from './IRoleManager';
import { SecurityRoleAsString } from './RoleManager._types';

const AccountIdProperty = 'accountId';
export default class RoleManager implements IRoleManager {
    private _accountId: number = null;
    private _role: SecurityRole = null;

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
    public get currentRole() {
        if (this._role !== null) {
            return this._role;
        }

        const role = (Object.keys(SecurityRole) as SecurityRoleAsString[]) //
            .find(this._isRoleActive);

        return this._role = (
            role === undefined
                ? SecurityRole.Anonymous
                : SecurityRole[role]
        );
    }

    public get isAdministrator() {
        return this.currentRole === SecurityRole.Administrator;
    }

    private _isRoleActive = (role: SecurityRoleAsString) => {
        return this._rootElement.classList.contains(SecurityRole[role]);
    }
}
