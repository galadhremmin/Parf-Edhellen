export const capitalize = (words: string): string => typeof words === 'string' //
    ? words.split(' ').map((w: string) => w.substr(0, 1).toLocaleUpperCase() + w.substr(1)).join(' ') //
    : null;

const EmptyStringExpression = /^\s*$/;
export const isEmptyString = (s: string): boolean => {
    if (s === null || s === undefined) {
        return true;
    }

    if (typeof s !== 'string') {
        return isEmptyString(String(s));
    }

    return EmptyStringExpression.test(s);
};
