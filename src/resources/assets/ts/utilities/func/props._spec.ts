import {
    describe,
    expect,
    test,
} from '@jest/globals';

import {
    excludeProps,
    pickProps,
} from './props';

describe('utilities/func/props', () => {
    test('picks props from an object', () => {
        const i = {
            x: 10,
            y: 20,
            z: 30,
        };
        const e = {
            x: i.x,
            z: i.z,
        };

        let a = excludeProps(i, ['y']);
        expect(a).toEqual(e);

        a = pickProps(i, ['x', 'z']);
        expect(a).toEqual(e);
    });

    test('sanitizes bad input', () => {
        const i = {
            x: 10,
            y: 20,
            z: 30,
        };
        const e = {};

        const inputs = [ null, undefined, 0, 200, 'bad' ];

        for (const input of inputs) {
            let a = excludeProps(i, input as any);
            expect(a).toEqual(i);

            a = pickProps(i, input as any);
            expect(a).toEqual(e);
        }
    });

    test('supports empty prop names', () => {
        const i = {
            x: 10,
            y: 20,
            z: 30,
        };
        const e = {};

        let a = excludeProps(i, []);
        expect(a).toEqual(i);

        a = pickProps(i, []);
        expect(a).toEqual(e);
    });
});
