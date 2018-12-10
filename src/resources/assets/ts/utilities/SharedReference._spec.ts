/* tslint:disable:max-classes-per-file */
import { expect } from 'chai';
import { ApplicationGlobalPrefix } from '../config';
import SharedReference from './SharedReference';

declare var window: any; // node

class TestClass {
    private static _count = 0;

    constructor() {
        TestClass._count += 1;
    }

    public get count() {
        return TestClass._count;
    }
}

class TestClass2 {
    public static shared = false;
}

describe('utilities/SharedReference', () => {
    it('is instantiated and part of window', () => {
        const expectedGlobalName = `${ApplicationGlobalPrefix}.TestClass`;
        const ref = new SharedReference(TestClass);

        expect(ref.value).to.be.instanceof(TestClass);
        expect(window[expectedGlobalName]).to.equal(ref.value);
    });

    it('should only be instantiated once', () => {
        for (let i = 0; i < 4; i += 1) {
            const ref = new SharedReference(TestClass);
            expect(ref.value.count).to.equal(1);
        }
    });

    it('should fail initialization', () => {
        expect(() => {
            const ref = new SharedReference(TestClass2);
        }).to.throw(`Type TestClass2 is configured to disallow shared references.`);
    });
});
