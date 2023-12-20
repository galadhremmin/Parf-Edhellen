import { CanBeConstructed } from '@root/_types';
import { ComponentClass, FunctionComponent } from 'react';
import {
    DIContainerType,
} from './config._types';

const diContainer: {
    [C in keyof DIContainerType]?: () => DIContainerType[C];
} = {};

export function setSingleton<K extends keyof DIContainerType, C extends CanBeConstructed<any>>(key: K, constructor: C) {
    let instance: InstanceType<C> = null;
    diContainer[key] = () => {
        if (instance === null) {
            instance = new constructor();
        }
        return instance;
    };
}

export function setInstance<K extends keyof DIContainerType, C extends CanBeConstructed<any>>(key: K, constructor: C) {
    diContainer[key] = () => new constructor();
}

export function resolve<T extends keyof DIContainerType>(name: T) {
    const factory = diContainer[name];
    if (typeof factory !== 'function') {
        throw new Error(`Failed to resolve ${name}. DI container contains: ${Object.keys(diContainer).join(', ')}`);
    }

    const instance = factory();
    return instance;
}

export function withPropInjection<P>(
    UnderlyingComponent: FunctionComponent<P> | ComponentClass<P>,
    injectProps: { [key in keyof P]?: keyof DIContainerType },
): FunctionComponent<P> | ComponentClass<P> {
    return function DIComponent(props: P) {
        const resolved: Partial<P> = {};
        const injectableProps = Object.keys(injectProps);

        for (const prop of injectableProps) {
            const key = prop as keyof P;
            if (! props[key]) {
                resolved[key] = resolve(injectProps[key]) as any;
            }
        }

        const finalProps = {
            ...resolved,
            ...props,
        };
        return <UnderlyingComponent {...finalProps} />;
    }
}
