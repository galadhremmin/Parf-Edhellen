import {
    describe,
    expect,
    test,
} from '@jest/globals';
import { stringHash, stringHashAll } from './hashing';

describe('utilities/func/hashing', () => {
    test('hashes correctly', () => {
        const input = [
            'a', 'b', 'revenge', 'revenue',
        ];
        const expected = [
            97, 98, 1099842154, 1099842588,
        ];
        const actual = input.map(stringHash);

        expect(actual).toEqual(expected);
    });

    test('hashes multiple components', () => {
        const actual = stringHashAll('a', 'b', 'c');
        const expected = stringHash('a|b|c');

        expect(actual).toEqual(expected);
    });
});
