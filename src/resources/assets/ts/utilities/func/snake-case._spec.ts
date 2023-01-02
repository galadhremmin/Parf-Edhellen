import {
    describe,
    expect,
    test,
} from '@jest/globals';
import {
    camelCaseFromSnakeCase,
    propsToSnakeCase,
    snakeCasePropsToCamelCase,
    toSnakeCase,
} from './snake-case';

describe('utilities/func/snake-case', () => {
    test('converts camelCase to snake_case', () => {
        const input = 'aSmallStepForAMan';
        const expected = 'a_small_step_for_a_man';
        const actual = toSnakeCase(input);

        expect(actual).toEqual(expected);
    });

    test('converts CamelCase to snake_case', () => {
        const input = 'ASmallStepForAMan';
        const expected = 'a_small_step_for_a_man';
        const actual = toSnakeCase(input);

        expect(actual).toEqual(expected);
    });

    test('converts camelCase to snake-case (- as delimiter)', () => {
        const input = 'aSmallStepForAMan';
        const expected = 'a-small-step-for-a-man';
        const actual = toSnakeCase(input, '-');

        expect(actual).toEqual(expected);
    });

    test('converts CamelCase to snake-case (- as delimiter)', () => {
        const input = 'ASmallStepForAMan';
        const expected = 'a-small-step-for-a-man';
        const actual = toSnakeCase(input, '-');

        expect(actual).toEqual(expected);
    });

    test('converts an object with camelCase props to snake_case', () => {
        const obj = {
            aSmallStepForAMan: 10,
            oneGiantLeapForMankind: 20,
        };
        const expected = {
            a_small_step_for_a_man: 10,
            one_giant_leap_for_mankind: 20,
        };

        const actual = propsToSnakeCase<any>(obj);
        expect(Object.keys(actual)).toEqual(Object.keys(expected));
        expect(actual.a_small_step_for_a_man).toEqual(expected.a_small_step_for_a_man);
        expect(actual.one_giant_leap_for_mankind).toEqual(expected.one_giant_leap_for_mankind);
    });

    test('converts snake_case to camelCase', () => {
        const input = 'we_are_not_yet_on_mars';
        const expected = 'weAreNotYetOnMars';

        const actual = camelCaseFromSnakeCase(input);
        expect(actual).toEqual(expected);
    });

    test('converts snake-case (- as delimiter) to camelCase', () => {
        const input = 'we-are-not-yet-on-mars';
        const expected = 'weAreNotYetOnMars';

        const actual = camelCaseFromSnakeCase(input, '-');
        expect(actual).toEqual(expected);
    });

    test('converts _snake_case to _camelCase', () => {
        const snakes = [
            ['_we_are_not_yet_on_mars', '_weAreNotYetOnMars'],
            ['__lan_gladh_iol', '__lanGladhIol'],
        ];

        for (const snake of snakes) {
            const actual = camelCaseFromSnakeCase(snake[0]);
            expect(actual).toEqual(snake[1]);
        }
    });

    test('converts snake_case object to camelCase object', () => {
        const input = {
            mars: {
                apparent_magnitude: [-2.94, +1.86],
                argument_of_perihelion: 286.502,
                composition: [
                    { v: 95.97, name: 'carbon dioxide' },
                    { v: 1.93, name: 'argon' },
                    { v: 1.89, name: 'nitrogen' },
                    { v: 0.146, name: 'oxygen' },
                ],
                eccentricity: 0.0934,
                longitude_of_ascending_node: 49.558,
                orbital_period: 686.971,
                temperature: {
                    maximum_temperature: 308,
                    minimum_temperature: 130,
                },
            },
        };

        const expected = {
            mars: {
                apparentMagnitude: [-2.94, +1.86],
                argumentOfPerihelion: 286.502,
                composition: [
                    { v: 95.97, name: 'carbon dioxide' },
                    { v: 1.93, name: 'argon' },
                    { v: 1.89, name: 'nitrogen' },
                    { v: 0.146, name: 'oxygen' },
                ],
                eccentricity: 0.0934,
                longitudeOfAscendingNode: 49.558,
                orbitalPeriod: 686.971,
                temperature: {
                    maximumTemperature: 308,
                    minimumTemperature: 130,
                },
            },
        };

        const actual = snakeCasePropsToCamelCase(input);
        expect(actual).toEqual(expected);
    });
});
