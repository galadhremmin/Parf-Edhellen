import { expect } from 'chai';

import {
    excludeProps,
    pickProps,
} from './props';

describe('utilities/func/props', () => {
    it('picks props from an object', () => {
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
        expect(a).to.deep.equal(e);

        a = pickProps(i, ['x', 'z']);
        expect(a).to.deep.equal(e);
    });

    it('sanitizes bad input', () => {
        const i = {
            x: 10,
            y: 20,
            z: 30,
        };
        const e = {};

        const inputs = [ null, undefined, 0, 200, 'bad' ];

        for (const input of inputs) {
            let a = excludeProps(i, input as any);
            expect(a).to.deep.equal(i);

            a = pickProps(i, input as any);
            expect(a).to.deep.equal(e);
        }
    });

    it('supports empty prop names', () => {
        const i = {
            x: 10,
            y: 20,
            z: 30,
        };
        const e = {};

        let a = excludeProps(i, []);
        expect(a).to.deep.equal(i);

        a = pickProps(i, []);
        expect(a).to.deep.equal(e);
    });
});
