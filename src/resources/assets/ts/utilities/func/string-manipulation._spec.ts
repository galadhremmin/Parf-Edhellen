import {
    describe,
    expect,
    test,
} from '@jest/globals';
import {
    capitalize,
    isEmptyString,
} from './string-manipulation';

describe('utilities/func/string-manipulation', () => {
    test('capitalizes one word', () => {
        const input = 'thôn';
        const expected = 'Thôn';

        const actual = capitalize(input);
        expect(actual).toEqual(expected);
    });

    test('converts invalid values to null', () => {
        const inputs: any[] = [undefined, '', null, 1, {}];
        const expecteds = [null, '', null, null, null];

        for (let i = 0; i < inputs.length; i += 1) {
            const actual = capitalize(inputs[i]);
            expect(actual).toEqual(expecteds[i]);
        }
    });

    test('converts multiple words', () => {
        const input = 'aerlinn in edhil o imladris';
        const expected = 'Aerlinn In Edhil O Imladris';

        const actual = capitalize(input);
        expect(actual).toEqual(expected);
    });

    test('identifies empty strings', () => {
        const s = [ '', null, undefined, '    ', '    b ' ];
        const e = [ true, true, true, true, false ];

        for (let i = 0; i < s.length; i += 1) {
            expect(isEmptyString(s[i])).toEqual(e[i]);
        }
    });
});
