import { expect } from 'chai';
import { SecurityRole } from '../config';
import RoleManager from './RoleManager';

describe('security/RoleManager', () => {
    let rootElement: HTMLElement;
    before(() => {
        rootElement = document.createElement('div');
    });

    afterEach(() => {
        rootElement.className = '';
    });

    it('no active role, but finds default anonymous', () => {
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).to.equal(SecurityRole.Anonymous);
        expect(manager.currentRole).to.equal(SecurityRole.Anonymous); // testing memory cache
    });

    it('supports anonymous role', () => {
        rootElement.classList.add(SecurityRole.Anonymous);
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).to.equal(SecurityRole.Anonymous);
        expect(manager.currentRole).to.equal(SecurityRole.Anonymous); // testing memory cache
    });

    it('supports user role', () => {
        rootElement.classList.add(SecurityRole.User);
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).to.equal(SecurityRole.User);
        expect(manager.currentRole).to.equal(SecurityRole.User); // testing memory cache
    });

    it('supports admin role', () => {
        rootElement.classList.add(SecurityRole.Administrator);
        const manager = new RoleManager(rootElement);
        expect(manager.currentRole).to.equal(SecurityRole.Administrator);
        expect(manager.currentRole).to.equal(SecurityRole.Administrator); // testing memory cache
    });

    it('no active account ID, supports default 0', () => {
        const manager = new RoleManager(rootElement);
        expect(manager.accountId).to.equal(0);
    });

    it('supports account ID', () => {
        const accountId = 5;
        rootElement.setAttribute('data-account-id', String(accountId));
        const manager = new RoleManager(rootElement);
        expect(manager.accountId).to.equal(accountId);
    });
});
