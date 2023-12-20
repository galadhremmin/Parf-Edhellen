export type EventListener = (evt: CustomEvent) => void;

export interface IEventMap {
    [eventName: string]: EventListener;
}

export type EventListenerOrName = EventListener | string;

export default interface IGlobalEvents {
    /**
     * Associates the specified listener function with the `loadEntity` event. This event
     * is used for every search entity that is not associated with the glossary.
     */
    set loadEntity(listenerFunc: EventListenerOrName);

    /**
     * Gets the `loadEntity` event's name as a string.
     */
    get loadEntity(): EventListenerOrName;

    /**
     * Associates the specified listener function with the `loadGlossary` event.
     */
    set loadGlossary(listenerFunc: EventListenerOrName);

    /**
     * Gets the `loadGlossary` event's name as string.
     */
    get loadGlossary(): EventListenerOrName;

    /**
     * Associates the specified listener function with the `loadReference` event.
     */
    set loadReference(listenerFunc: EventListenerOrName);

    /**
     * Gets the `loadReference` event's name as string.
     */
    get loadReference(): EventListenerOrName;

    /**
     * Specifies the global error handler.
     */
    set errorLogger(listenerFunc: EventListenerOrName);

    /**
     * Retrieves the current global error handler
     */
    get errorLogger(): EventListenerOrName;

    /**
     * Fires the specified event with details `T`.
     * @param eventName event name string.
     * @param detail object with details or `null`.
     */
    fire<T>(eventName: EventListenerOrName, detail?: T): void;

    /**
     * Disconnects listeners from all global events.
     */
    disconnect(): void;
}
