import { expect } from 'chai';
import convert from './convert';

interface IOrigin {
    x?: number;
    y?: string;
    z?: boolean;
    n?: any;
}

interface IDestination {
    a?: number;
    b?: string;
    c?: boolean;
    d?: any;
}

describe('utilities/func/convert', () => {
    it('can convert non-complex types', () => {
        const o: IOrigin = {
            x: 10,
            y: 'hello world',
            z: false,
        };

        const d = convert<IOrigin, IDestination>({
            a: 'x',
            b: 'y',
            c: 'z',
        }, o);

        expect(d.a).to.equal(o.x);
        expect(d.b).to.equal(o.y);
        expect(d.c).to.equal(o.z);
        expect(d.d).to.be.undefined;
    });

    it('can convert complex types', () => {
        const complexType = {
            hello: 'world',
        };

        const o: IOrigin = {
            n: complexType,
            x: 10,
            y: 'hello world',
            z: false,
        };

        const d = convert<IOrigin, IDestination>({
            a: (p) => Math.sqrt(p.x),
            b: undefined,
            c: null,
            d: 'n',
        }, o);

        expect(d.a).to.equal(Math.sqrt(o.x));
        expect(d.b).to.be.undefined;
        expect(d.c).to.be.null;
        expect(d.d).to.equal(o.n);
    });

    it('returns null when given null', () => {
        const d = convert<IOrigin, IDestination>({}, null);
        expect(d).to.be.null;
    });

    it('returns null when given undefined', () => {
        const d = convert<IOrigin, IDestination>({}, undefined);
        expect(d).to.be.null;
    });

    it('returns null when given NaN as a number', () => {
        const d = convert<number, IDestination>({}, NaN);
        expect(d).to.be.null;
    });
});
