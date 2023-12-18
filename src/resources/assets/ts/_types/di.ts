export type CanBeConstructed<T extends CanBeConstructed<any>> = (
        new (...args: any) => InstanceType<T>
    ) & {
        shared?: boolean;
        name?: string;
    };
