import {
	describe,
	expect,
	test,
} from '@jest/globals';

import classNames from '@root/utilities/ClassNames';

describe('utilities/ClassNames', () => {
	test('joins simple strings', () => {
		expect(classNames('a', 'b', 'c')).toBe('a b c');
	});

	test('handles arrays and nested arrays', () => {
		expect(classNames(['a', ['b']], 'c')).toBe('a b c');
	});

	test('handles object maps with truthy values', () => {
		expect(classNames({ a: true, b: false, c: 1 })).toBe('a c');
	});

	test('ignores falsy and nullish values', () => {
		expect(classNames(null, undefined, false, 0, '', 'x')).toBe('x');
	});

	test('deduplicates repeated values', () => {
		expect(classNames('a', 'a', { a: true }, ['a'])).toBe('a');
	});

	test('accepts numbers', () => {
		expect(classNames(1, 2, { 3: true })).toBe('1 2 3');
	});

	test('does not trim values', () => {
		expect(classNames(' a ', 'b', { ' c ': true })).toBe('a b c');
		expect(classNames('   ', 'a')).toBe('a');
		expect(classNames({ ' x ': true, '  ': true })).toBe('x');
	});
});


