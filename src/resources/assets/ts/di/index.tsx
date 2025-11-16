import type { CanBeConstructed } from '@root/_types';
import type { ComponentClass, FunctionComponent } from 'react';
import { useRef } from 'react';
import type {
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

export function resolve<T extends keyof DIContainerType>(name: T): DIContainerType[T] {
    const factory = diContainer[name];
    if (typeof factory !== 'function') {
        throw new Error(`Failed to resolve ${name}. DI container contains: ${Object.keys(diContainer).join(', ')}`);
    }

    return factory();
}

export function withPropInjection<P>(
    UnderlyingComponent: FunctionComponent<P> | ComponentClass<P>,
    injectProps: { [key in keyof P]?: keyof DIContainerType },
): FunctionComponent<P> | ComponentClass<P> {
    return function DIComponent(props: P) {
        const resolved = useRef<Partial<P>>(null);

        // This is *required* to maintain non-singleton instances across component renders. Without references,
        // non-singleton instances will be reconstructed every render.
        if (resolved.current === null) {
            const nextResolved: Partial<P> = {};
            const injectableProps = Object.keys(injectProps);

            for (const prop of injectableProps) {
                const key = prop as keyof P;
                if (! props[key]) {
                    nextResolved[key] = resolve(injectProps[key]) as any;
                }
            }

            resolved.current = nextResolved;
        }

        const finalProps = {
            ...resolved.current,
            ...props,
        };
        return <UnderlyingComponent {...finalProps} />;
    }
}
