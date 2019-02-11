import {
    ComponentEventHandler,
    IComponentEvent,
    IDefaultComponent,
} from './Component._types';

export const fireEvent = <V>(component: IDefaultComponent, ev: ComponentEventHandler<V>, value: V = undefined,
    async: boolean = false) => {
    if (component === undefined) {
        throw new Error('Component reference is undefined.');
    }

    let name: string = null;
    if (component !== null) {
        const {
            id: componentId,
            name: componentName,
        } = component.props;

        name = componentName || componentId || (component as any).displayName || null;
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

export const fireEventAsync = <V>(component: IDefaultComponent, ev: ComponentEventHandler<V>, value: V = undefined) =>
    fireEvent(component, ev, value, true);
