import React from 'react';

import { isEmptyString } from '@root/utilities/func/string-manipulation';

import { fireEvent } from '../Component';
import { IComponentEvent } from '../Component._types';
import {
    IProps,
    IState,
} from './TagInput._types';
import TagLabel from './TagLabel';

export default class TagInput extends React.Component<IProps, IState> {
    public static defaultProps = {
        value: [],
    } as Partial<IProps>;

    public state = {
        textValue: '',
    };

    public render() {
        const {
            _onBlur,
            _onDeleteTag,
            _onKeyPress,
            _onTextChange,
        } = this;

        const {
            textValue,
        } = this.state;

        const tags = this._getTags();

        return <>
            {tags.map((tag: string) => <TagLabel key={tag} tag={tag} onDelete={_onDeleteTag} />)}
            <input className="form-control"
                onBlur={_onBlur}
                onChange={_onTextChange}
                onKeyPress={_onKeyPress}
                type="text"
                value={textValue}
            />
        </>;
    }

    private _getTags() {
        let {
            value: tags,
        } = this.props;

        console.log(tags);
        if (! Array.isArray(tags)) {
            tags = [];
        }

        return tags;
    }

    private _addTag(tag: string) {
        this._setTextValue('');

        // Protect against incorrect `null` and empty string (or whitespace).
        if (isEmptyString(tag)) {
            return;
        }

        const {
            onChange,
        } = this.props;

        const tags = this._getTags();

        // Do not add the tag if it already exists
        if (tags.indexOf(tag) > -1) {
            return;
        }

        tags.push(tag);
        tags.sort(); // TODO: locale compare to sort alphabetically

        fireEvent(name, onChange, tags);
    }

    private _deleteTag(tag: string) {
        const tags = [ ...this._getTags() ];

        const pos = tags.indexOf(tag);
        if (pos > -1) {
            tags.splice(pos, 1);
            fireEvent(name, this.props.onChange, tags);
        }
    }

    private _setTextValue(textValue: string) {
        this.setState({
            textValue,
        });
    }

    private _onDeleteTag = (ev: IComponentEvent<string>) => {
        this._deleteTag(ev.value);
    }

    private _onTextChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        this._setTextValue(ev.target.value);
    }

    private _onBlur = () => {
        const {
            textValue,
        } = this.state;

        this._addTag(textValue);
    }

    private _onKeyPress = (ev: React.KeyboardEvent<HTMLInputElement>) => {
        const {
            textValue,
        } = this.state;

        if (ev.which === 13) {
            ev.preventDefault();
            this._addTag(textValue);
        }
    }
}
