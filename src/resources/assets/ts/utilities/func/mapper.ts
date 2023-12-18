type ConversionTable<S extends Partial<Record<keyof S, unknown>>, D extends Partial<Record<keyof D, unknown>>> = {
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
export const mapper = <S extends Partial<Record<keyof S, unknown>>, D extends Partial<Record<keyof D, unknown>>>(table: ConversionTable<S, D>, subject: S, resolverArgs: unknown[] = []): D => {
    if (isIneligible(subject)) {
        return null;
    }

    const props = Object.keys(table) as (keyof D)[];
    const result = {} as D;

    for (const prop of props) {
        const resolver = table[prop];
        let value; // = undefined;

        switch (typeof resolver) {
            case 'function':
                value = resolver.apply(this, [ subject, ...resolverArgs ]);
                break;
            case 'string':
                value = (subject as unknown)[resolver];
                break;
            default:
                value = resolver;
        }

        if (value !== undefined) {
            result[prop] = value;
        }
    }

    return result;
};

/**
 * Converts an array of `S` to an array of `D`.
 * @param table
 * @param subjects
 */
export const mapArray = <S extends Partial<Record<keyof S, unknown>>, D extends Partial<Record<keyof D, unknown>>>(table: ConversionTable<S, D>, subjects: S[]): D[] => {
    if (isIneligible(subjects)) {
        return [];
    }

    return subjects.map((s, i) => mapper(table, s, [i]));
};

export const mapArrayGroupByMap = <S extends Partial<Record<keyof S, unknown>>, D extends Partial<Record<keyof D, unknown>>, G = string>(table: ConversionTable<S, D>, subjects: S[], groupBy: (v: S) => G): Map<G, D[]> => {
    const map = new Map<G, D[]>();
    if (isIneligible(subjects)) {
        return map;
    }

    for (const subject of subjects) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const groupName = groupBy.call(subjects, subject) as G;
        if (! map.has(groupName)) {
            map.set(groupName, []);
        }
        const group = map.get(groupName);
        group.push(mapper(table, subject, [group.length]));
    }

    return map;
};

export const mapArrayGroupBy = <S extends Partial<Record<keyof S, unknown>>, D extends Partial<Record<keyof D, unknown>>>(table: ConversionTable<S, D>, subjects: S[], groupBy: (v: S) => string): Record<string, D[]> => {
    const map: Record<string, D[]> = {};
    if (isIneligible(subjects)) {
        return map;
    }

    for (const subject of subjects) {
        // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
        const groupName = groupBy.call(subjects, subject) as string;
        if (map[groupName] === undefined) {
            map[groupName] = [];
        }
        const group = map[groupName];
        group.push(mapper(table, subject, [group.length]));
    }

    return map;
};

