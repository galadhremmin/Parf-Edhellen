import classNames from '@root/utilities/ClassNames';
import type { KeyboardEvent } from 'react';

import { fireEventAsync } from '@root/components/Component';
import { FormComponent } from '@root/components/Form/FormComponent';
import TextIcon from '@root/components/TextIcon';
import type {
    IProps,
    ISearchComponentProps,
} from './SearchQueryInput._types';

export default class SearchQueryInput extends FormComponent<string, IProps, ISearchComponentProps> {
    public get componentPropNames() {
        return ['autoFocus'];
    }

    public render() {
        const {
            loading,
            showEnterHint,
        } = this.props;

        const fieldClasses = classNames('form-control', { disabled: loading });
        const icon = loading ? 'refresh' : 'search';
        const iconClassName = loading ? 'loading' : '';
        const componentProps = this.pickComponentProps();

        return <div className="input-group input-group-lg">
            <span className="input-group-text">
                <TextIcon icon={icon} className={iconClassName} />
            </span>
            <input accessKey="s"
                autoCapitalize="off"
                autoComplete="off"
                className={fieldClasses}
                onChange={this.onBackingComponentChange}
                onKeyDown={this._onKeyDown}
                placeholder="What are you looking for?"
                type="search"
                {...componentProps}
            />
            {showEnterHint && <span className="input-group-text SearchQueryInput--enter-hint">
                Enter &crarr;
            </span>}
        </div>;
    }

    private _onKeyDown = (ev: KeyboardEvent<HTMLInputElement>) => {
        let direction = 0;
        switch (ev.key) {
            case 'ArrowUp': // up
                direction = -1;
                break;
            case 'Enter': // enter
            case 'ArrowDown': // down
                direction = +1;
                break;
        }

        if (!direction) {
            return;
        }

        void fireEventAsync(this, this.props.onSearchResultNavigate, direction);
    }
}
