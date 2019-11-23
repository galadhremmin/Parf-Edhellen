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
 * @param subject
 */
export const mapArray = <S, D>(table: ConversionTable<S, D>, subject: S[]): D[] => {
    if (isIneligible(subject)) {
        return [];
    }

    return subject.map((s, i) => mapper(table, s, [i]));
};
