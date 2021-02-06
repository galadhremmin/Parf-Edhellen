type ConversionTable<S, D> = {
    [R in keyof D]?: (keyof S) | ((v: S, index?: number) => D[R]) | null;
};

const isIneligible = <T>(subject: T) => //
    subject === null ||
    subject === undefined ||
    (typeof subject === 'number' && (isNaN(subject) || !isFinite(subject)));

/**
 * Converts an entity `S` to `D` with optional resolver arguments.
 * @param table source to destination value map
 * @param subject source entity
 * @param resolverArgs optional arguments to pass to the resolver function in the conversion table.
 */
export const mapper = <S, D>(table: ConversionTable<S, D>, subject: S, resolverArgs: any[] = []): D => {
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

    return result as D;
};

/**
 * Converts an array of `S` to an array of `D`.
 * @param table
 * @param subjects
 */
export const mapArray = <S, D>(table: ConversionTable<S, D>, subjects: S[]): D[] => {
    if (isIneligible(subjects)) {
        return [];
    }

    return subjects.map((s, i) => mapper(table, s, [i]));
};

export const mapArrayGroupBy = <S, D, G = string>(table: ConversionTable<S, D>, subjects: S[], groupBy: (v: S) => G): Map<G, D[]> => {
    const map = new Map<G, D[]>();
    if (isIneligible(subjects)) {
        return map;
    }

    for (const subject of subjects) {
        const groupName = groupBy.call(subjects, subject);
        if (! map.has(groupName)) {
            map.set(groupName, []);
        }
        const group = map.get(groupName);
        group.push(mapper(table, subject, [group.length]));
    }

    return map;
};