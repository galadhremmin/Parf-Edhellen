import {
    ComponentEventHandler,
    ComponentFiredEvent,
    ComponentOrName,
    IComponentEvent,
} from './Component._types';

interface INamedComponent {
    displayName?: string;
}

export const fireEvent = <V>(componentOrName: ComponentOrName, ev: ComponentEventHandler<V>, value: V = undefined,
    async = false): ComponentFiredEvent => {
    if (componentOrName === undefined) {
        throw new Error('Component reference is undefined.');
    }

    let name: string = null;
    if (typeof componentOrName === 'string') {
        if (componentOrName && componentOrName.length > 0) {
            name = componentOrName;
        }

    } else if (componentOrName) {
        const {
            id: componentId,
            name: componentName,
        } = componentOrName.props;

        name = componentName || componentId || (componentOrName as INamedComponent).displayName || null;
    }

    if (typeof ev !== 'function') {
        return Promise.resolve(false);
    }

    const args: IComponentEvent<V> = {
        name,
        value,
    };

    if (async) {
        return new Promise((resolve) => {
            requestIdleCallback(() => {
                ev(args);
                resolve(true);
            });
        });
    } else {
        ev(args);
    }

    return Promise.resolve(true);
};

export const fireEventAsync = <V>(componentOrName: ComponentOrName, ev: ComponentEventHandler<V>,
    value: V = undefined) => fireEvent(componentOrName, ev, value, true);
