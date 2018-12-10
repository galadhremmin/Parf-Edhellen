export type EventListener = (evt: Event) => void;

export interface IEventMap {
    [eventName: string]: EventListener;
}

export type EventListenerOrName = EventListener | string;
