import React, { createRef } from 'react';

import { isEmptyString } from '@root/utilities/func/string-manipulation';

import { fireEvent } from '../../Component';
import { IComponentEvent } from '../../Component._types';
import {
    IProps,
    IState,
} from './TagInput._types';
import TagLabel from './TagLabel';

import './TagInput.scss';

export default class TagInput extends React.Component<IProps, IState> {
    public static defaultProps = {
        value: [],
    } as Partial<IProps>;

    public state = {
        textValue: '',
    };

    private _inputRef = createRef<HTMLInputElement>();

    public render() {
        const {
            name,
        } = this.props;

        const {
            _onBlur,
            _onDeleteTag,
            _onKeyDown,
            _onTextChange,
        } = this;

        const {
            textValue,
        } = this.state;

        const tags = this._getTags();

        return <>
            <div className="TagInput--values">
                {tags.map((tag: string) => <TagLabel key={tag} tag={tag} onDelete={_onDeleteTag} />)}
            </div>
            <input ref={this._inputRef}
                className="form-control"
                id={name}
                onBlur={_onBlur}
                onChange={_onTextChange}
                onKeyDown={_onKeyDown}
                type="text"
                value={textValue}
            />
        </>;
    }

    private _getTags(clone = false) {
        let {
            value: tags,
        } = this.props;

        if (! Array.isArray(tags)) {
            tags = [];
        }

        return clone ? [ ...tags ] : tags;
    }

    private _addTag(tag: string) {
        this._setTextValue('');

        // Protect against incorrect `null` and empty string (or whitespace).
        if (isEmptyString(tag)) {
            return;
        }

        const {
            name,
            onChange,
        } = this.props;

        const tags = this._getTags(/* clone: */ true);

        // Do not add the tag if it already exists
        if (tags.indexOf(tag) > -1) {
            return;
        }

        tags.push(tag.trim());
        tags.sort((a, b) => a.toLocaleLowerCase().localeCompare(b.toLocaleLowerCase()));

        void fireEvent(name, onChange, tags);
    }

    private _deleteTag(tag: string) {
        const {
            name,
            onChange,
        } = this.props;
        const tags = this._getTags(/* clone: */ true);

        const pos = tags.indexOf(tag);
        if (pos > -1) {
            tags.splice(pos, 1);
            void fireEvent(name, onChange, tags);
        }
    }

    private _setTextValue(textValue: string) {
        this.setState({
            textValue,
        });
    }

    private _onDeleteTag = (ev: IComponentEvent<string>) => {
        this._deleteTag(ev.value);

        if (this._inputRef.current !== null) {
            this._inputRef.current.focus();
        }
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

    private _onKeyDown = (ev: React.KeyboardEvent<HTMLInputElement>) => {
        const {
            textValue,
        } = this.state;

        if (ev.key === 'Enter') {
            ev.preventDefault();
            this._addTag(textValue);
        }
    }
}
