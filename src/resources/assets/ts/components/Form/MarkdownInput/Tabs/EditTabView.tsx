import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import Markdown from '@root/components/Markdown';
import { LearnMoreMarkdownUrl } from '@root/config';
import debounce from '@root/utilities/func/debounce';
import { IProps, IState } from './EditTabView._types';

import './EditTabView.scss';

export default class EditTabView extends React.Component<IProps, IState> {
    private _triggerPreview = debounce(500, () => {
        const {
            value,
        } = this.props;

        this.setState({
            previewValue: value,
        });
    });

    constructor(props: IProps) {
        super(props);
        this.state = {
            previewValue: props.value || null,
        };
    }

    public render() {
        const {
            enter2Paragraph,
            id,
            name,
            required,
            rows,
            value,
        } = this.props;

        const {
            previewValue,
        } = this.state;

        return <>
            <div className="EditTabView--split-view">
                <div className="EditTabView--split-view__input">
                    <textarea className="form-control"
                            id={id}
                            name={name}
                            onChange={this._onMarkdownChange}
                            onKeyDown={this._onMarkdownKeyDown}
                            required={required}
                            rows={rows}
                            value={value}
                            placeholder="Click or tap here to start typing."
                    />
                </div>
                <div className="EditTabView--split-view__preview">
                    <Markdown parse={true} text={previewValue} />
                </div>
            </div>
            <div>
                <div className="checkbox text-right">
                    <label>
                        <input type="checkbox" checked={enter2Paragraph} onChange={this._onEnter2ParagraphChange} />
                        Enter key inserts a paragraph (&para;)
                    </label>
                </div>
                <small className="pull-right">
                    {' Supports Markdown. '}
                    <a href={LearnMoreMarkdownUrl} target="_blank">
                        Read more (opens a new window)
                    </a>.
                </small>
            </div>
        </>;
    }

    private _triggerChange(newValue: string) {
        const {
            name,
            onChange,
        } = this.props;

        fireEvent(name, onChange, newValue);
        this._triggerPreview();
    }

    private _onEnter2ParagraphChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        const {
            name,
            onEnter2ParagraphChange,
        } = this.props;

        fireEvent(name, onEnter2ParagraphChange, ev.target.checked);
    }

    private _onMarkdownChange = (ev: React.ChangeEvent<HTMLTextAreaElement>) => {
        this._triggerChange(ev.target.value);
    }

    private _onMarkdownKeyDown = (ev: React.KeyboardEvent<HTMLTextAreaElement>) => {
        const {
            enter2Paragraph,
        } = this.props;

        // special behavior for enter key: insert paragraph, unless shift key is pressed
        if (enter2Paragraph === false || ev.shiftKey) {
            return;
        }

        const target = (ev.target as HTMLTextAreaElement);
        const pos = target.selectionStart;

        if (ev.which === 13 && pos !== undefined) {
            ev.preventDefault();
            ev.stopPropagation();

            const newValue = target.value.substr(0, pos) + `\n\n` + target.value.substr(pos + 1);
            this._triggerChange(newValue);
        }
    }
}
