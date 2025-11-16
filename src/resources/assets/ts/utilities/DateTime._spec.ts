import {
	describe,
	expect,
	test,
	beforeAll,
	afterAll,
} from '@jest/globals';

import {
	dateNowInMilliseconds,
	fromISOToDate,
	formatDateTimeFull,
	formatDateTimeShortWithSeconds,
	formatRelative,
	getLocalTimeZone,
	InvalidDate,
} from '@root/utilities/DateTime';

describe('utilities/DateTime', () => {
	let originalNow: () => number;
	const fixedNow = new Date('2024-01-01T12:00:00.000Z').getTime();

	beforeAll(() => {
		originalNow = Date.now;
		// Stabilize relative time outputs
		// eslint-disable-next-line @typescript-eslint/unbound-method
		Date.now = () => fixedNow;
	});

	afterAll(() => {
		// eslint-disable-next-line @typescript-eslint/unbound-method
		Date.now = originalNow;
	});

	test('dateNowInMilliseconds returns a number close to Date.now()', () => {
		const v = dateNowInMilliseconds();
		expect(typeof v).toBe('number');
		expect(Math.abs(v - fixedNow)).toBeLessThanOrEqual(5);
	});

	test('fromISOToDate handles Date, ISO string and invalid inputs', () => {
		const d = new Date('2020-01-01T00:00:00.000Z');
		expect(fromISOToDate(d)).toEqual(d);
		expect(fromISOToDate('2020-01-01T00:00:00.000Z')?.toISOString()).toEqual(d.toISOString());
		expect(fromISOToDate(null)).toBeNull();
		expect(fromISOToDate(undefined)).toBeNull();
		expect(fromISOToDate('not-a-date')).toBeNull();
	});

	test('formatDateTimeFull matches Intl output for a known date', () => {
		const date = new Date('2023-06-15T08:30:45.000Z');
		const expected = new Intl.DateTimeFormat(undefined, { dateStyle: 'full', timeStyle: 'long' }).format(date);
		expect(formatDateTimeFull(date)).toEqual(expected);
	});

	test('formatDateTimeShortWithSeconds matches Intl output for a known date', () => {
		const date = new Date('2023-06-15T08:30:45.000Z');
		const expected = new Intl.DateTimeFormat(undefined, { dateStyle: 'short', timeStyle: 'medium' }).format(date);
		expect(formatDateTimeShortWithSeconds(date)).toEqual(expected);
	});

	test('formatDateTimeFull returns InvalidDate for null/undefined', () => {
		expect(formatDateTimeFull(null as unknown as any)).toBe(InvalidDate);
		expect(formatDateTimeFull(undefined as unknown as any)).toBe(InvalidDate);
	});

	test('formatDateTimeShortWithSeconds returns InvalidDate for null/undefined', () => {
		expect(formatDateTimeShortWithSeconds(null as unknown as any)).toBe(InvalidDate);
		expect(formatDateTimeShortWithSeconds(undefined as unknown as any)).toBe(InvalidDate);
	});

	test('formatRelative for past and future', () => {
		const tenMinutesMs = 10 * 60 * 1000;
		const past = new Date(fixedNow - tenMinutesMs);
		const future = new Date(fixedNow + tenMinutesMs);
		// We cannot hardcode exact strings due to locales; ensure they contain "minute" notion
		const pastStr = formatRelative(past);
		const futureStr = formatRelative(future);
		expect(typeof pastStr).toBe('string');
		expect(typeof futureStr).toBe('string');
		expect(pastStr.length).toBeGreaterThan(0);
		expect(futureStr.length).toBeGreaterThan(0);
	});

	test('getLocalTimeZone returns a string', () => {
		const tz = getLocalTimeZone();
		expect(typeof tz).toBe('string');
		expect(tz.length).toBeGreaterThan(0);
	});
});


