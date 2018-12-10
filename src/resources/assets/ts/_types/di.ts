export interface INewable<T> {
    shared?: boolean;
    new(...args: any[]): T;
}
