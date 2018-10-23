import classNames from 'classnames';
import * as React from 'react';

import { IFormComponentProps } from '../../../_types/components';

interface IProps extends IFormComponentProps<string> {
    autofocus?: boolean;
    loading?: boolean;
    onChange: (query: string) => void;
}

export default class SearchQueryInput extends React.PureComponent<IProps> {
    public onChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const value = ev.target.value;
        this.props.onChange(value);
    }

    public render() {
        const fieldClasses = classNames('form-control', { disabled: this.props.loading });
        const statusClasses = classNames('glyphicon', this.props.loading
            ? 'glyphicon-refresh loading' : 'glyphicon-search');

        return <div className="input-group input-group-lg">
            <span className="input-group-addon">
                <span className={statusClasses}>&#32;</span>
            </span>
            <input type="search" className={fieldClasses}
                placeholder="What are you looking for?"
                tabIndex={this.props.tabIndex}
                accessKey="s"
                name={this.props.name}
                autoComplete="off"
                autoCapitalize="off"
                autoFocus={this.props.autofocus}
                value={this.props.value}
                onChange={this.onChange} />
        </div>;
    }
}
