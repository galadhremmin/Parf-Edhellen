import classNames from 'classnames';
import * as React from 'react';

import { FormComponent } from '../../../components/FormComponent';
import {
    IComponentProps,
    IProps,
} from './SearchQueryInput.types';

export default class SearchQueryInput extends FormComponent<string, IProps, IComponentProps> {
    public get componentPropNames() {
        return ['autoFocus'];
    }

    public render() {
        const fieldClasses = classNames('form-control', { disabled: this.props.loading });
        const statusClasses = classNames('glyphicon', this.props.loading
            ? 'glyphicon-refresh loading' : 'glyphicon-search');
        const componentProps = this.pickComponentProps();

        return <div className="input-group input-group-lg">
            <span className="input-group-addon">
                <span className={statusClasses}>&#32;</span>
            </span>
            <input accessKey="s"
                autoCapitalize="off"
                autoComplete="off"
                className={fieldClasses}
                onChange={this.onChange}
                placeholder="What are you looking for?"
                type="search"
                {...componentProps}
            />
        </div>;
    }
}
