/**
 * Returns the current date and time in milliseconds.
 * @returns The current date and time in milliseconds.
 */
export function dateNowInMilliseconds(): number {
	return Date.now();
}

/**
 * Converts an ISO string or Date object to a Date object.
 * @param value - The ISO string or Date object to convert.
 * @returns The Date object.
 */
export function fromISOToDate(value: string | Date | null | undefined): Date | null {
	if (!value) {
		return null;
	}
	if (value instanceof Date) {
		return value;
	}
	const d = new Date(value);
	return isNaN(d.getTime()) ? null : d;
}

/**
 * Formats a date and time into a full date and time string.
 * @param value - The date and time to format.
 * @returns The formatted date and time string.
 */
export function formatDateTimeFull(value: string | Date): string {
	const date = value instanceof Date ? value : new Date(value);
	// Similar to Luxon DateTime.DATETIME_FULL
	// Use dateStyle/timeStyle if available, otherwise fall back to explicit options.
	try {
		return new Intl.DateTimeFormat(undefined, { dateStyle: 'full', timeStyle: 'long' }).format(date);
	} catch {
		return new Intl.DateTimeFormat(undefined, {
			weekday: 'long',
			year: 'numeric',
			month: 'long',
			day: 'numeric',
			hour: 'numeric',
			minute: '2-digit',
			second: '2-digit',
		}).format(date);
	}
}

/**
 * Formats a date and time into a short date and time string with seconds.
 * @param value - The date and time to format.
 * @returns The formatted date and time string.
 */
export function formatDateTimeShortWithSeconds(value: string | Date): string {
	const date = value instanceof Date ? value : new Date(value);
	// Similar to Luxon DateTime.DATETIME_SHORT_WITH_SECONDS
	try {
		return new Intl.DateTimeFormat(undefined, { dateStyle: 'short', timeStyle: 'medium' }).format(date);
	} catch {
		return new Intl.DateTimeFormat(undefined, {
			year: '2-digit',
			month: '2-digit',
			day: '2-digit',
			hour: '2-digit',
			minute: '2-digit',
			second: '2-digit',
		}).format(date);
	}
}

type RelativeUnit = 'year' | 'month' | 'day' | 'hour' | 'minute' | 'second';

/**
 * Formats a date and time into a relative date and time string.
 * @param value - The date and time to format.
 * @returns The formatted date and time string.
 */
export function formatRelative(value: string | Date): string {
	const date = value instanceof Date ? value : new Date(value);
	const now = Date.now();
	const diffMs = date.getTime() - now;
	const absMs = Math.abs(diffMs);

	const msPer: Record<RelativeUnit, number> = {
		year: 365 * 24 * 60 * 60 * 1000,
		month: 30 * 24 * 60 * 60 * 1000,
		day: 24 * 60 * 60 * 1000,
		hour: 60 * 60 * 1000,
		minute: 60 * 1000,
		second: 1000,
	};

	let unit: RelativeUnit = 'second';
	for (const u of ['year', 'month', 'day', 'hour', 'minute', 'second'] as RelativeUnit[]) {
		if (absMs >= msPer[u]) {
			unit = u;
			break;
		}
	}
	const valueNum = Math.round(absMs / msPer[unit]);

	try {
		const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });
		return rtf.format(diffMs < 0 ? -valueNum : valueNum, unit);
	} catch {
		// Basic fallback
		const s = `${valueNum} ${unit}${valueNum !== 1 ? 's' : ''}`;
		return diffMs < 0 ? `${s} ago` : `in ${s}`;
	}
}

/**
 * Gets the local time zone from the browser's Intl API.
 * @returns The local time zone or 'UTC' if the browser does not support Intl.
 */
export function getLocalTimeZone(): string {
	try {
		return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
	} catch {
		return 'UTC';
	}
}


