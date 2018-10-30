import { expect} from 'chai';
import {
    propsToSnakeCase,
    toSnakeCase,
} from './snake-case';

describe('utilities/func/snake-case', () => {
    it('converts camelCase to snake_case', () => {
        const input = 'aSmallStepForAMan';
        const expected = 'a_small_step_for_a_man';
        const actual = toSnakeCase(input);

        expect(actual).to.equal(expected);
    });

    it('converts CamelCase to snake_case', () => {
        const input = 'ASmallStepForAMan';
        const expected = 'a_small_step_for_a_man';
        const actual = toSnakeCase(input);

        expect(actual).to.equal(expected);
    });

    it('converts an object with camelCase props to snake_case', () => {
        const obj = {
            aSmallStepForAMan: 10,
            oneGiantLeapForMankind: 20,
        };
        const expected = {
            a_small_step_for_a_man: 10,
            one_giant_leap_for_mankind: 20,
        };

        const actual = propsToSnakeCase(obj);
        expect(Object.keys(actual)).to.deep.equal(Object.keys(expected));
        expect(actual.a_small_step_for_a_man).to.equal(expected.a_small_step_for_a_man);
        expect(actual.one_giant_leap_for_mankind).to.equal(expected.one_giant_leap_for_mankind);
    });
});
