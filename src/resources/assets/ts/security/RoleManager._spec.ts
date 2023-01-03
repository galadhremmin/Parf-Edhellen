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
        expect(manager.currentRole).toEqual(SecurityRole.Anonymous);
        expect(manager.currentRole).toEqual(SecurityRole.Anonymous); // testing memory cache
    });

    test('supports anonymous role', () => {
        rootElement.classList.add(SecurityRole.Anonymous);
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).toEqual(SecurityRole.Anonymous);
        expect(manager.currentRole).toEqual(SecurityRole.Anonymous); // testing memory cache
    });

    test('supports user role', () => {
        rootElement.classList.add(SecurityRole.User);
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).toEqual(SecurityRole.User);
        expect(manager.currentRole).toEqual(SecurityRole.User); // testing memory cache
    });

    test('supports admin role', () => {
        rootElement.classList.add(SecurityRole.Administrator);
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).toEqual(SecurityRole.Administrator);
        expect(manager.currentRole).toEqual(SecurityRole.Administrator); // testing memory cache
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
