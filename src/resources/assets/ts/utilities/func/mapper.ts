type ConversionTable<T1, T2> = {
    [R in keyof T2]?: (keyof T1) | ((v: T1, index?: number) => T2[R]) | null;
};

const isIneligible = <T>(subject: T) => //
    subject === null ||
    subject === undefined ||
    (typeof subject === 'number' && (isNaN(subject) || !isFinite(subject)));

export const mapper = <T1, T2>(table: ConversionTable<T1, T2>, subject: T1, resolverArgs: any[] = []): T2 => {
    if (isIneligible(subject)) {
        return null;
    }

    const props = Object.keys(table);
    const result: any = {};

    for (const prop of props) {
        const resolver = (table as any)[prop];
        let value; // = undefined;

        switch (typeof resolver) {
            case 'function':
                value = resolver.apply(this, [ subject, ...resolverArgs ]);
                break;
            case 'string':
                value = (subject as any)[resolver];
                break;
            default:
                value = resolver;
        }

        if (value !== undefined) {
            result[prop] = value;
        }
    }

    return result as T2;
};

/**
 * Converts an array of `T1` to an array of `T2`.
 * @param table
 * @param subject
 */
export const mapArray = <T1, T2>(table: ConversionTable<T1, T2>, subject: T1[]): T2[] => {
    if (isIneligible(subject)) {
        return [];
    }

    return subject.map((s, i) => mapper(table, s, [i]));
};
