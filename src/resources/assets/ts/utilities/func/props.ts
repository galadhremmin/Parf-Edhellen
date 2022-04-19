export const excludeProps = <T extends object>(props: T, propNames: (keyof T)[]) => {
    if (! isValid(props, propNames)) {
        return props;
    }

    const namesToPick = (Object.keys(props) as (keyof T)[])
        .filter((propName) => propNames.indexOf(propName) === -1);

    return pickProps<T>(props, namesToPick);
};

export const pickProps = <T extends object>(props: T, propNames: (keyof T)[]) => {
    if (! isValid(props, propNames)) {
        return {};
    }

    return propNames.reduce((carry, propName) => {
        const value = props[propName];
        if (value === undefined) {
            return carry;
        }

        return {
            ...carry,
            [propName]: value,
        };
    }, {});
};

export const isValid = <T>(props: T, propNames: (keyof T)[]) => {
    return typeof props === 'object' &&
        Array.isArray(propNames) &&
        propNames.length > 0;
};
