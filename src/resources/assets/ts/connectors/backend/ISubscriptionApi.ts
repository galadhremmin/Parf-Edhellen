export interface ISubscriptionStatus {
    subscribed: boolean;
}

export interface ISubscriptionApi {
    isSubscribed(entityName: string, id: number): Promise<ISubscriptionStatus>;
    subscribe(entityName: string, id: number): Promise<ISubscriptionStatus>;
    unsubscribe(entityName: string, id: number): Promise<ISubscriptionStatus>;
}
