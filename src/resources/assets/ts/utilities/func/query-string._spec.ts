import { describe, expect, test } from "@jest/globals";
import { buildQueryString, parseQueryString } from "./query-string";

describe('utilities/func/query-string', () => {
    test('parses a search string accurately', () => {
        const searchString = 'a=1&b=123&c=hello%20world!&d[]=1&d[]=2&d[]=3&e=true&f=false';
        const obj = parseQueryString(searchString);

        expect(obj.a).toEqual(1);
        expect(obj.b).toEqual(123);
        expect(obj.c).toEqual('hello world!');
        expect(obj.d).toEqual([1,2,3]);
        expect(obj.e).toEqual(true);
        expect(obj.f).toEqual(false);
    });

    test('builds a search string accurately', () => {
        const values = {
            a: 1,
            b: 123,
            c: 'hello world!',
            d: [1,2,3],
            e: true,
            f: false,
        };
        const searchString = buildQueryString(values);
        expect(searchString).toEqual('a=1&b=123&c=hello+world!&d[]=1&d[]=2&d[]=3&e=true&f=false');
    });
});
