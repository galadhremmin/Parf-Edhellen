export const excludeProps = <T>(props: T, propNames: Array<keyof T>) => {
    if (! isValid(props, propNames)) {
        return props;
    }

    const namesToPick = Object.keys(props)
        .filter((propName) => propNames.indexOf(propName as any) === -1);

    return pickProps<T>(props, namesToPick as any);
};

export const pickProps = <T>(props: T, propNames: Array<keyof T>) => {
    if (! isValid(props, propNames)) {
        return {};
    }

    return propNames.reduce((carry, propName) => {
        const value = (props as any)[propName];
        if (value === undefined) {
            return carry;
        }

        return {
            ...carry,
            [propName]: value,
        };
    }, {});
};

export const isValid = <T>(props: T, propNames: Array<keyof T>) => {
    return typeof props === 'object' &&
        Array.isArray(propNames) &&
        propNames.length > 0;
};
