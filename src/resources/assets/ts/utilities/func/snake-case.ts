export const toSnakeCase = (s: string) => {
    return s.replace(/^([A-Z])/, (substr: string) => substr.toLowerCase())
        .replace(/([A-Z]{1})/g, (substr: string) => '_' + substr.toLowerCase());
};

export const propsToSnakeCase = (obj: any) => {
    const props = Object.keys(obj);
    const result: any = {};

    for (const prop of props) {
        result[toSnakeCase(prop)] = obj[prop];
    }

    return result;
};
