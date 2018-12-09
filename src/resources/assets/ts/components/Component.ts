import React from 'react';
import {
    ComponentEventHandler,
    IComponentEvent,
    IDefaultComponent,
} from './Component._types';

export const fireEvent = <V>(component: IDefaultComponent, ev: ComponentEventHandler<V>, value: V) => {
    const {
        id,
        name,
    } = component.props;

    if (typeof ev !== 'function') {
        return false;
    }

    const args: IComponentEvent<V> = {
        name: name || id || (component as any).displayName || null,
        value,
    };

    window.setTimeout(() => {
        ev(args);
    }, 0);

    return true;
};
