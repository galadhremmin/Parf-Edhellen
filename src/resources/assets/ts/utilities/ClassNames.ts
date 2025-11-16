type ClassValue = string | number | null | undefined | false | ClassValue[] | { [key: string]: any };

function _addToClassNames(value: ClassValue, classNames: Set<string>) {
	if (! value) {
		return;
	}

	if (typeof value === 'string' || typeof value === 'number') {
		const trimmedValue = String(value).trim();
		if (trimmedValue.length > 0) {
			classNames.add(trimmedValue);
		}

	} else if (Array.isArray(value)) {
        value.forEach(v => _addToClassNames(v, classNames));

	} else if (typeof value === 'object') {
		Object.keys(value).filter(key => String(key).trim().length > 0).forEach(key => {
			if (value[key]) {
				classNames.add(key.trim());
			}
		});
	}
}

/**
 * Joins class names into a single string.
 * Supports strings, numbers, arrays and object maps with truthy values.
 * 
 * @param args - The class names to add to the set.
 * @returns The joined class names.
 */
export default function classNames(...args: ClassValue[]): string {
	const out = new Set<string>();
	(args || []).forEach(arg => _addToClassNames(arg, out));
	return Array.from(out).join(' ');
}

