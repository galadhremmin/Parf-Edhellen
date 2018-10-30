import { expect } from 'chai';
import { stringHash } from './hashing';

describe('utilities/func/hashing', () => {
    it('hashes correctly', () => {
        const input = [
            'a', 'b', 'revenge', 'revenue',
        ];
        const expected = [
            97, 98, 1099842154, 1099842588,
        ];
        const actual = input.map(stringHash);

        expect(actual).to.deep.equal(expected);
    });
});
