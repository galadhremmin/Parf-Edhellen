import { expect } from 'chai';
import {
    mapArrayGroupBy,
    mapper,
} from './mapper';

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

        const d = mapper<IOrigin, IDestination>({
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

        const d = mapper<IOrigin, IDestination>({
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
        const d = mapper<IOrigin, IDestination>({}, null);
        expect(d).to.be.null;
    });

    it('returns null when given undefined', () => {
        const d = mapper<IOrigin, IDestination>({}, undefined);
        expect(d).to.be.null;
    });

    it('returns null when given NaN as a number', () => {
        const d = mapper<number, IDestination>({}, NaN);
        expect(d).to.be.null;
    });

    it ('can group by a property', () => {
        const o: IOrigin[] = [];
        const numberOfElementsInTestSet = 10;
        
        for (let i = 1; i <= numberOfElementsInTestSet; i += 1) {
            o.push({
                n: i % 2,
                x: 20,
                y: '30',
                z: true,
            });
        };

        const a = mapArrayGroupBy<IOrigin, IDestination>({
            a: 'x',
            b: 'y',
            c: 'z',
        }, o, (v) => v.n);

        const keys = Array.from(a.keys());

        expect(keys).to.have.lengthOf(2);
        expect(keys).to.contain(0);
        expect(keys).to.contain(1);

        for (const key of keys) {
            const values = a.get(key);
            expect(values).to.have.lengthOf(numberOfElementsInTestSet/2);
            const expectedValues = o.filter((v) => v.n == key).map<IDestination>((v) => ({
                a: v.x,
                b: v.y,
                c: v.z,
            }));

            expect(values).to.deep.equal(expectedValues);
        }
    });
});
