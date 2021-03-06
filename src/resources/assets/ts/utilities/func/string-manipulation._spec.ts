import { expect } from 'chai';
import {
    capitalize,
    isEmptyString,
} from './string-manipulation';

describe('utilities/func/string-manipulation', () => {
    it('capitalizes one word', () => {
        const input = 'thôn';
        const expected = 'Thôn';

        const actual = capitalize(input);
        expect(actual).to.equal(expected);
    });

    it('converts invalid values to null', () => {
        const inputs: any[] = [undefined, '', null, 1, {}];
        const expecteds = [null, '', null, null, null];

        for (let i = 0; i < inputs.length; i += 1) {
            const actual = capitalize(inputs[i]);
            expect(actual).to.equal(expecteds[i]);
        }
    });

    it('converts multiple words', () => {
        const input = 'aerlinn in edhil o imladris';
        const expected = 'Aerlinn In Edhil O Imladris';

        const actual = capitalize(input);
        expect(actual).to.equal(expected);
    });

    it('identifies empty strings', () => {
        const s = [ '', null, undefined, '    ', '    b ' ];
        const e = [ true, true, true, true, false ];

        for (let i = 0; i < s.length; i += 1) {
            expect(isEmptyString(s[i]), `"${s[i]}" did not yield ${e[i]}.`).to.equal(e[i]);
        }
    });
});
