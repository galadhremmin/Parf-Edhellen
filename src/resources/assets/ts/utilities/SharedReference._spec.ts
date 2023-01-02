/* tslint:disable:max-classes-per-file */
import {
    describe,
    expect,
    test,
} from '@jest/globals';
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
    test('is instantiated and part of window', () => {
        const expectedGlobalName = `${ApplicationGlobalPrefix}.TestClass`;
        const ref = new SharedReference(TestClass);

        expect(ref.value).toBeInstanceOf(TestClass);
        expect(window[expectedGlobalName]).toEqual(ref.value);
    });

    test('should only be instantiated once', () => {
        for (let i = 0; i < 4; i += 1) {
            const ref = new SharedReference(TestClass);
            expect(ref.value.count).toEqual(1);
        }
    });

    test('should fail initialization', () => {
        expect(() => {
            const ref = new SharedReference(TestClass2);
        }).toThrow(`Type TestClass2 is configured to disallow shared references.`);
    });
});
