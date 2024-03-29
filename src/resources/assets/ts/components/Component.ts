import {
    ComponentEventHandler,
    ComponentOrName,
    IComponentEvent,
} from './Component._types';

interface INamedComponent {
    displayName?: string;
}

export const fireEvent = <V>(componentOrName: ComponentOrName, ev: ComponentEventHandler<V>, value: V = undefined,
    async = false) => {
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
        return false;
    }

    const args: IComponentEvent<V> = {
        name,
        value,
    };

    if (async) {
        window.setTimeout(() => {
            ev(args);
        }, 0);
    } else {
        ev(args);
    }

    return true;
};

export const fireEventAsync = <V>(componentOrName: ComponentOrName, ev: ComponentEventHandler<V>,
    value: V = undefined) => fireEvent(componentOrName, ev, value, true);
