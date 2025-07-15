import {
    afterEach,
    beforeEach,
    describe,
    expect,
    test,
} from '@jest/globals';
import { SecurityRole } from '../config';
import RoleManager from './RoleManager';

describe('security/RoleManager', () => {
    let rootElement: HTMLElement;
    beforeEach(() => {
        rootElement = document.createElement('div');
    });

    afterEach(() => {
        rootElement.className = '';
    });

    test('no active role, but finds default anonymous', () => {
        const manager = new RoleManager(rootElement);
        expect(manager.currentRoles).toEqual([SecurityRole.Anonymous]);
        expect(manager.currentRoles).toEqual([SecurityRole.Anonymous]); // testing memory cache
    });

    test('supports anonymous role', () => {
        rootElement.dataset.accountRoles = [SecurityRole.Anonymous].join(',');
        const manager = new RoleManager(rootElement);
        expect(manager.currentRoles).toEqual([SecurityRole.Anonymous]);
        expect(manager.currentRoles).toEqual([SecurityRole.Anonymous]); // testing memory cache
    });

    test('supports user role', () => {
        rootElement.dataset.accountRoles = [SecurityRole.User].join(',');
        const manager = new RoleManager(rootElement);
        expect(manager.currentRoles).toEqual([SecurityRole.User]);
        expect(manager.currentRoles).toEqual([SecurityRole.User]); // testing memory cache
    });

    test('supports admin role', () => {
        rootElement.dataset.accountRoles = [SecurityRole.Administrator].join(',');
        const manager = new RoleManager(rootElement);
        expect(manager.currentRoles).toEqual([SecurityRole.Administrator]);
        expect(manager.currentRoles).toEqual([SecurityRole.Administrator]); // testing memory cache
    });

    test('supports multiple roles', () => {
        rootElement.dataset.accountRoles = [SecurityRole.Administrator, SecurityRole.Discuss].join(',');
        const manager = new RoleManager(rootElement);
        expect(manager.currentRoles).toEqual([SecurityRole.Administrator, SecurityRole.Discuss]);
        expect(manager.currentRoles).toEqual([SecurityRole.Administrator, SecurityRole.Discuss]); // testing memory cache
    });

    test('supports Anonymous suppression', () => {
        rootElement.dataset.accountRoles = [SecurityRole.Administrator, SecurityRole.Anonymous].join(',');
        const manager = new RoleManager(rootElement);
        expect(manager.currentRoles).toEqual([SecurityRole.Administrator]);
        expect(manager.currentRoles).toEqual([SecurityRole.Administrator]); // testing memory cache
    });
    test('no active account ID, supports default 0', () => {
        const manager = new RoleManager(rootElement);
        expect(manager.accountId).toEqual(0);
    });

    test('supports account ID', () => {
        const accountId = 5;
        rootElement.setAttribute('data-account-id', String(accountId));
        const manager = new RoleManager(rootElement);
        expect(manager.accountId).toEqual(accountId);
    });
});
