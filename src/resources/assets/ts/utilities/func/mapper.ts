type ConversionTable<T1, T2> = {
    [R in keyof T2]?: (keyof T1) | ((v: T1) => T2[R]) | null;
};

const isIneligible = <T>(subject: T) => //
    subject === null ||
    subject === undefined ||
    (typeof subject === 'number' && (isNaN(subject) || !isFinite(subject)));

export const mapper = <T1, T2>(table: ConversionTable<T1, T2>, subject: T1): T2 => {
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
                value = resolver(subject);
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

export const mapArray = <T1, T2>(table: ConversionTable<T1, T2>, subject: T1[]): T2[] => {
    if (isIneligible(subject)) {
        return [];
    }

    return subject.map((s) => mapper(table, s));
};
