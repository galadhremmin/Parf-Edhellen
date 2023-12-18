import { CanBeConstructed } from '@root/_types';
import { ComponentClass, FunctionComponent, useState } from 'react';
import {
    DIContainerType,
} from './config._types';

var diContainer: {
    [K in keyof DIContainerType]?: () => DIContainerType[K];
} = {};

export function singleton<K extends keyof DIContainerType, C extends CanBeConstructed<any>>(key: K, constructor: C) {
    let instance: InstanceType<C> = null;
    diContainer[key] = () => {
        if (instance === null) {
            instance = new constructor();
        }
        return instance;
    };
}

export function resolve<T extends keyof DIContainerType>(name: T) {
    const factory = diContainer[name];
    if (typeof factory !== 'function') {
        throw new Error(`Failed to resolve ${name}. DI container contains: ${Object.keys(diContainer).join(', ')}`);
    }

    const instance = factory();
    return instance;
};

export function withPropResolving<P>(
    Component: FunctionComponent<P> | ComponentClass<P>,
    injectProps: { [key in keyof P]?: keyof DIContainerType },
): FunctionComponent<P> | ComponentClass<P> {
    return (props: P) => {
        const [ resolvedProps ] = useState(() => {
            const resolved: Partial<P> = {};
            for (const prop of Object.keys(injectProps)) {
                const key = prop as keyof P;
                resolved[key] = resolve(injectProps[key]) as any;
            }

            return resolved;
        });

        const finalProps = {
            ...resolvedProps,
            ...props,
        };
        return <Component {...finalProps} />;
    };
}
