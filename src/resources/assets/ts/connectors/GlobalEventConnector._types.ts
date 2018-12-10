export type EventListener = (evt: CustomEvent) => void;

export interface IEventMap {
    [eventName: string]: EventListener;
}

export type EventListenerOrName = EventListener | string;
