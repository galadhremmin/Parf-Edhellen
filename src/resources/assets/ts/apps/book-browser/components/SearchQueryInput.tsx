import classNames from 'classnames';
import React from 'react';

import { fireEventAsync } from '@root/components/Component';
import { FormComponent } from '@root/components/Form/FormComponent';
import TextIcon from '@root/components/TextIcon';
import {
    IComponentProps,
    IProps,
} from './SearchQueryInput._types';

export default class SearchQueryInput extends FormComponent<string, IProps, IComponentProps> {
    public get componentPropNames() {
        return ['autoFocus'];
    }

    public render() {
        const {
            loading,
        } = this.props;

        const fieldClasses = classNames('form-control', { disabled: loading });
        const icon = loading ? 'refresh' : 'search';
        const iconClassName = loading ? 'loading' : '';
        const componentProps = this.pickComponentProps();

        return <div className="input-group input-group-lg">
            <span className="input-group-addon">
                <TextIcon icon={icon} className={iconClassName} />
            </span>
            <input accessKey="s"
                autoCapitalize="off"
                autoComplete="off"
                className={fieldClasses}
                onChange={this.onChange}
                onKeyDown={this._onKeyDown}
                placeholder="What are you looking for?"
                type="search"
                {...componentProps}
            />
        </div>;
    }

    private _onKeyDown = (ev: React.KeyboardEvent<HTMLInputElement>) => {
        let direction = 0;
        switch (ev.which) {
            case 38: // up
                direction = -1;
                break;
            case 13: // enter
            case 40: // down
                direction = +1;
                break;
        }

        if (!direction) {
            return;
        }

        fireEventAsync(this, this.props.onSearchResultNavigate, direction);
    }
}
