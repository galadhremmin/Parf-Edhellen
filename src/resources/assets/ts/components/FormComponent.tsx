/* tslint:disable:interface-name */
import React from 'react';

export interface IBackingComponentProps<V> {
    name?: string;
    tabIndex?: number;
    required?: boolean;
    value?: V;
}

export interface IComponentProps<V> extends IBackingComponentProps<V> {
    onChange?: (ev: IChangeEvent<V>) => void;
}

export interface IChangeEvent<V> {
    name: string;
    value: V;
}

const DefaultComponentPropNames: Array<keyof IBackingComponentProps<any>> = //
    ['name', 'value', 'required', 'tabIndex'];

export interface FormComponent {
    /**
     * Optional getter which overrides the `DefaultComponentPropNames` configuration.
     */
    componentPropNames?: string[];

    /**
     * Optional getter which determines whether the prop names returned from `componentPropNames`
     * should be appended to the default configuration. Defaults to `true`.
     */
    appendComponentPropNames?: boolean;
}

export const integerConverter = (value: string) => parseInt(value, 10);
export const floatConverter = (value: string) => parseFloat(value);
export const booleanConverter = (value: string | boolean) => value === 'on' || value === 'true' || value === true;

export abstract class FormComponent<V = any, P = {}, CP = {}, S = {}, SS = any>
    extends React.PureComponent<P & IComponentProps<V>, S & IBackingComponentProps<V>, SS> {

    /**
     * Default value converter for string values.
     * @param value value from backing component
     */
    protected convertValue(value: string) {
        return value as any as V;
    }

    /**
     * Default change event handler for backing component.
     */
    protected onChange = (ev: React.ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) => {
        const onChangeExternal = this.props.onChange;
        if (onChangeExternal === undefined) {
            ev.preventDefault();
            return;
        }

        const name = this.props.name;
        if (name === undefined) {
            throw new Error('You have to give the component a name.');
        }

        const value = ev.target.value;
        onChangeExternal({
            name,
            value: this.convertValue(value),
        });
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
            props[propName] = allProps[propName];
        }

        return props;
    }
}
