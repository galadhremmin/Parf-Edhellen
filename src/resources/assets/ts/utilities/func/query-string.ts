import { isEmptyString } from "./string-manipulation";

export type QueryStringValue = boolean | number | string;

export interface IQueryStringObject {
    [key: string]: QueryStringValue | QueryStringValue[];
}

export const parseQueryString = (queryString: string): IQueryStringObject => {
    const parser = new URLSearchParams(queryString);
    const obj: IQueryStringObject = {};
    const pendingArrays = new Map<string, QueryStringValue[]>();

    for (const [key, value] of parser.entries()) {
        const formattedValue = parseQueryStringValue(value);
        if (formattedValue === undefined) {
            // Skip empty values
            continue;
        }
        
        if (key.endsWith('[]')) {
            if (pendingArrays.has(key)) {
                pendingArrays.get(key).push(formattedValue);
            } else {
                pendingArrays.set(key, [formattedValue]);
            }
        } else {
            obj[key] = formattedValue;
        }
    }

    for (const [key, value] of pendingArrays) {
        obj[key.substring(0, key.length - 2)] = value;
    }

    return obj;
};

export const buildQueryString = (obj: IQueryStringObject): string => {
    const parser = new URLSearchParams();
    for (const [key, value] of Object.entries(obj)) {
        if (Array.isArray(value)) {
            value.forEach((item) => parser.append(`${key}[]`, String(item)));
        } else {
            parser.set(key, String(value));
        }
    }
    return decodeURIComponent(parser.toString());
}

const parseQueryStringValue = (value: string): QueryStringValue => {
    if (isEmptyString(value)) {
        return undefined;
    }
    
    if (/^(true|false)$/i.test(value)) {
        return 'TRUE' === value.toUpperCase();
    }
    
    if (! isNaN(+value) && isFinite(+value)) {
        return parseFloat(value);
    }
    
    return value;
};
