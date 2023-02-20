/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import React from 'react';
import {
    fireEvent,
} from '../Component';
import {
    IBackingComponentProps,
    IComponentProps,
} from './FormComponent._types';

const DefaultComponentPropNames: (keyof IBackingComponentProps<any>)[] = //
    ['className', 'name', 'value', 'required', 'tabIndex'];

const isNull = (value: any) => value === undefined || value === null;
export const integerConverter = (value: string) => isNull(value) ? 0 : parseInt(value, 10);
export const floatConverter = (value: string) => isNull(value) ? 0.00 : parseFloat(value);
export const booleanConverter = (value: string | boolean) => isNull(value) ? false
    : (value === 'on' || value === 'true' || value === true);

/**
 * Represents a form component wrapping a HTML element (backing component).
 * `V` is the value type, `P` props type, `CP` backing component props type, `S` state type.
 */
export abstract class FormComponent<V = any, P = Record<string, unknown>, CP = Record<string, unknown>, S = Record<string, unknown>, SS = any>
    extends React.Component<P & IComponentProps<V>, S & IBackingComponentProps<V>, SS> {

    /**
     * Optional getter which overrides the `DefaultComponentPropNames` configuration.
     */
    public get componentPropNames() {
        return [] as string[];
    }

     /**
      * Optional getter which determines whether the prop names returned from `componentPropNames`
      * should be appended to the default configuration. Defaults to `true`.
      */
    public get appendComponentPropNames() {
        return true;
    }

    /**
     * Default value converter for string values.
     * @param value value from backing component
     */
    protected convertValue(value: string) {
        return value as unknown as V;
    }

    /**
     * Default change event handler for backing component.
     */
    protected onChange = (ev: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const value = this.convertValue(ev.target.value);
        if (value === this.props.value) {
            return;
        }

        if (! fireEvent(this, this.props.onChange, value)) {
            ev.preventDefault();
        }
    }

    /**
     * Picks the props that can be applied to the backing component from the root component.
     *
     * The method relies on `componentPropNames` that by default returns a default configuration;
     * override if you need customer properties. Use in conjunction with `appendComponentPropNames`
     * (which is `true` by default) if you want to prevent your custom configuration to merge with
     * default configuration.
     */
    protected pickComponentProps(): { [ PN in keyof (CP & IBackingComponentProps<V>) ]: (CP &
        IBackingComponentProps<V>)[PN] } {
        let propNames = this.componentPropNames;
        if (propNames === undefined) {
            propNames = DefaultComponentPropNames;
        } else if (this.appendComponentPropNames !== false) {
            propNames = propNames.concat(DefaultComponentPropNames);
        }

        const props: any = {};
        const allProps = this.props as any;

        for (const propName of propNames) {
            if (allProps[propName] !== undefined) {
                props[propName] = allProps[propName];
            }
        }

        return props;
    }
}
