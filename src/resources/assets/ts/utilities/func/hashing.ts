/* tslint:disable:no-bitwise */

export const stringHash = (str: string) => {
    let hash = 0;
    let i;
    let chr;

    if (!str || str.length === 0) {
        return hash;
    }

    for (i = 0; i < str.length; i++) {
        chr   = str.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
    }

    return hash;
};

export const stringHashAll = (...str: string[]) => {
    if (str.length < 1) {
        return 0;
    }
    return stringHash(str.join('|'));
};
