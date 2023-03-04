export function deepClone<T>(obj: T): T {
    if (typeof structuredClone !== 'function') {
        return deepCloneLegacy(obj);
    }

    return structuredClone(obj);
};

function deepCloneLegacy<T>(item: T): T {
    console.warn(`Using imperfect deep cloner on ${JSON.stringify(item)}. Browser doesn't support structuredClone.`);
    return JSON.parse(JSON.stringify(item));
}
