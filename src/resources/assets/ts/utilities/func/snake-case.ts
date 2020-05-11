/**
 * Converts the string subject from camelCase to snake_case.
 * @param s string subject
 */
export const toSnakeCase = (s: string, delimiter = '_') => {
    return s.replace(/^([A-Z])/, (substr: string) => substr.toLowerCase())
        .replace(/([A-Z]{1})/g, (substr: string) => delimiter + substr.toLowerCase());
};

/**
 * Converts the string subject from snake_case to camelCase.
 * @param s string subject
 */
export const camelCaseFromSnakeCase = (s: string, delimiter = '_') => {
    let length = 0;
    const words = s.split(delimiter) //
        .map((word: string, i: number) => {
            const ps = length === 0
                ? (word.length === 0 ? delimiter : word)
                : word.charAt(0).toUpperCase() + word.substr(1);

            length += word.length;
            return ps;
        });

    return words.join('');
};

/**
 * Converts the properties on `obj` from camelCase to snake_case and emits a new object
 * with all of its values intact.
 * @param obj object with camelCase properties.
 */
export const propsToSnakeCase = <T>(obj: any) => transform<T>(obj, toSnakeCase);

/**
 * Converts the properties on `obj` from snake_case to camelCase and emits a new object
 * with all of its values intact.
 * @param obj object with snake_case properties.
 */
export const snakeCasePropsToCamelCase = <T>(obj: any): T => transform<T>(obj, camelCaseFromSnakeCase);

const transform = <T>(obj: any, converter: (obj: any) => string): T => {
    // Handle `undefined` and `null` and treat as `null`
    if (obj === undefined || obj === null) {
        return null;
    }

    // Handle arrays by respecting their integrity, but check whether they contain objects.
    if (Array.isArray(obj)) {
        const arr = [];
        for (const v of obj) {
            arr.push(transform<T>(v, converter));
        }
        return arr as any;
    }

    // Handle non-objects by simply returning them untouched.
    if (typeof obj !== 'object') {
        return obj as any;
    }

    // Handle objects by recreating the object from scratch.
    const props = Object.keys(obj);
    const newObj: any = {};

    for (const prop of props) {
        const camelCaseProp = converter(prop);
        newObj[camelCaseProp] = transform(obj[prop], converter);
    }

    return newObj as any;
};
