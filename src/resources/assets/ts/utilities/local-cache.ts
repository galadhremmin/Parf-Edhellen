import Cache from './cache';

/**
 * Cache using `localStorage`.
 */
export default class LocalCache<T> extends Cache<T> {
    protected get storage() {
        return window.localStorage;
    }
}
