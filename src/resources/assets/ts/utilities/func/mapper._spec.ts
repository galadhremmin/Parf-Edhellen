import {
    describe,
    expect,
    test,
} from '@jest/globals';
import {
    mapArrayGroupBy,
    mapArrayGroupByMap,
    mapper
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
    test('can convert non-complex types', () => {
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

        expect(d.a).toEqual(o.x);
        expect(d.b).toEqual(o.y);
        expect(d.c).toEqual(o.z);
        expect(d.d).toBeUndefined();
    });

    test('can convert complex types', () => {
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

        expect(d.a).toEqual(Math.sqrt(o.x));
        expect(d.b).toBeUndefined();
        expect(d.c).toBeNull();
        expect(d.d).toEqual(o.n);
    });

    test('returns null when given null', () => {
        const d = mapper<IOrigin, IDestination>({}, null);
        expect(d).toBeNull();
    });

    test('returns null when given undefined', () => {
        const d = mapper<IOrigin, IDestination>({}, undefined);
        expect(d).toBeNull();
    });

    test('returns null when given NaN as a number', () => {
        const d = mapper<number, IDestination>({}, NaN);
        expect(d).toBeNull();
    });

    test('can group by a property using Map', () => {
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

        const a = mapArrayGroupByMap<IOrigin, IDestination>({
            a: 'x',
            b: 'y',
            c: 'z',
        }, o, (v) => v.n);

        const keys = Array.from(a.keys());

        expect(keys).toHaveLength(2);
        expect(keys).toContain(0);
        expect(keys).toContain(1);

        for (const key of keys) {
            const values = a.get(key);
            expect(values).toHaveLength(numberOfElementsInTestSet/2);
            // tslint:disable-next-line: triple-equals
            const expectedValues = o.filter((v) => v.n == key).map<IDestination>((v) => ({
                a: v.x,
                b: v.y,
                c: v.z,
            }));

            expect(values).toEqual(expectedValues);
        }
    });

    test('can group by a property using JSObject', () => {
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

        const keys = Object.keys(a);

        expect(keys).toHaveLength(2);
        expect(keys).toContain('0');
        expect(keys).toContain('1');

        for (const key of keys) {
            const values = a[key];
            expect(values).toHaveLength(numberOfElementsInTestSet/2);
            // tslint:disable-next-line: triple-equals
            const expectedValues = o.filter((v) => v.n == key).map<IDestination>((v) => ({
                a: v.x,
                b: v.y,
                c: v.z,
            }));

            expect(values).toEqual(expectedValues);
        }
    });
});
