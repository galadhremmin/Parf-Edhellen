import {
    GlobalEventErrorLogger,
    GlobalEventLoadEntity,
    GlobalEventLoadGlossary,
    GlobalEventLoadReference,
} from '../config';
import IGlobalEvents, {
    EventListenerOrName,
    IEventMap,
} from './IGlobalEvents';

export default class GlobalEventConnector implements IGlobalEvents {
    /**
     * Local in-memory collection of active listeners. There can only be one listener per
     * event *and* `GlobalEventConnector` instance.
     */
    private _listeners: IEventMap = {};

    /**
     * Gets a copy of all listeners with the event as the key.
     */
    public get listeners() {
        return Object.keys(this._listeners).reduce((carry, key) => ({
            ...carry,
            [key]: this._listeners[key],
        }), {}) as IEventMap;
    }

    /**
     * Associates the specified listener function with the `loadEntity` event. This event
     * is used for every search entity that is not associated with the glossary.
     */
    public set loadEntity(listenerFunc: EventListenerOrName) {
        this._connect(GlobalEventLoadEntity, listenerFunc);
    }

    /**
     * Gets the `loadEntity` event's name as a string.
     */
    public get loadEntity() {
        return GlobalEventLoadEntity;
    }

    /**
     * Associates the specified listener function with the `loadGlossary` event.
     */
    public set loadGlossary(listenerFunc: EventListenerOrName) {
        this._connect(GlobalEventLoadGlossary, listenerFunc);
    }

    /**
     * Gets the `loadGlossary` event's name as string.
     */
    public get loadGlossary() {
        return GlobalEventLoadGlossary;
    }

    /**
     * Associates the specified listener function with the `loadReference` event.
     */
    public set loadReference(listenerFunc: EventListenerOrName) {
        this._connect(GlobalEventLoadReference, listenerFunc);
    }

    /**
     * Gets the `loadReference` event's name as string.
     */
    public get loadReference() {
        return GlobalEventLoadReference;
    }

    /**
     * Specifies the global error handler.
     */
    public set errorLogger(listenerFunc: EventListenerOrName) {
        this._connect(GlobalEventErrorLogger, listenerFunc);
    }

    /**
     * Retrieves the current global error handler
     */
    public get errorLogger() {
        return GlobalEventErrorLogger;
    }

    /**
     * Fires the specified event with details `T`.
     * @param eventName event name string.
     * @param detail object with details or `null`.
     */
    public fire<T>(eventName: EventListenerOrName, detail: T = null) {
        if (typeof eventName !== 'string') {
            throw new Error(`Event name must be a string (not ${typeof eventName}).`);
        }

        const event = new CustomEvent(eventName, {
            detail,
        });
        window.dispatchEvent(event);
    }

    /**
     * Disconnects listeners from all global events.
     */
    public disconnect() {
        Object.keys(this._listeners).forEach((key) => {
            this._disconnect(key, this._listeners[key]);
        });
        this._listeners = {};
    }

    private _connect(eventName: string, listenerFunc: EventListenerOrName) {
        if (typeof listenerFunc === 'function') {
            this._disconnectIfExists(eventName);
            window.addEventListener(eventName, listenerFunc);
            this._listeners[eventName] = listenerFunc;
        } else {
            this._invalidListener(eventName, listenerFunc);
        }
    }

    private _disconnect(eventName: string, listenerFunc: EventListenerOrName) {
        if (typeof listenerFunc === 'function') {
            window.removeEventListener(eventName, listenerFunc);
            delete this._listeners[eventName];
        } else {
            this._invalidListener(eventName, listenerFunc);
        }
    }

    private _disconnectIfExists(eventName: string) {
        const listener = this._listeners[eventName];
        if (listener !== undefined) {
            this._disconnect(eventName, listener);
        }
    }

    private _invalidListener(eventName: string, listenerFunc: EventListenerOrName) {
        throw new Error(`Event listener for ${eventName} must be of type "function" (not ${typeof listenerFunc}).`);
    }
}
