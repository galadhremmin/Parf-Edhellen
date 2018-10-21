import Cache from './Cache';

/**
 * Cache using `sessionStorage`.
 */
export default class SessionCache<T> extends Cache<T> {
    protected get storage() {
        return window.sessionStorage;
    }
}
