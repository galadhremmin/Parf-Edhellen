import {
    ComponentEventHandler,
    ComponentFiredEvent,
    ComponentFiredEventAsync,
    ComponentOrName,
    IComponentEvent,
} from './Component._types';

interface INamedComponent {
    displayName?: string;
}

export const fireEvent = <V>(componentOrName: ComponentOrName, ev: ComponentEventHandler<V>, value: V = undefined): ComponentFiredEvent => {
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
    
    ev(args);
    return true;
};

export const fireEventAsync = <V>(componentOrName: ComponentOrName, ev: ComponentEventHandler<V>, value: V = undefined): ComponentFiredEventAsync => {
    return new Promise((resolve, reject) => {
        requestIdleCallback(() => {
            const fired = fireEvent(componentOrName, ev, value);
            if (fired) {
                resolve(true);
            } else {
                reject(new Error('Event not fired'));
            }
        });
    });
}
