import React from 'react';

import { fireEvent } from '@root/components/Component';
import Markdown from '@root/components/Markdown';
import { LearnMoreMarkdownUrl } from '@root/config';
import debounce from '@root/utilities/func/debounce';
import { IProps, IState } from './EditTabView._types';

import './EditTabView.scss';

const ParagraphDelimiter = `\n\n`;

export default class EditTabView extends React.Component<IProps, IState> {
    private _markdownEditor: HTMLTextAreaElement;
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
            caretPosition: null,
            previewValue: props.value || null,
        };
    }

    public componentDidUpdate() {
        const {
            caretPosition,
        } = this.state;

        if (caretPosition !== null) {
            // Set the caret position to the one held in component state. This is a one-time
            // operation used to recover caret position for when paragraphs (or other textarea
            // events are overridden).
            this._markdownEditor.selectionEnd = caretPosition;
            this.setState({
                caretPosition: null,
            });
        }
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
                            ref={this._setMarkdownEditorRef}
                            placeholder="Click or tap here to start typing."
                    />
                </div>
                <div className="EditTabView--split-view__preview">
                    <Markdown parse={true} text={previewValue} />
                </div>
            </div>
            <div className="text-end">
                <div className="checkbox">
                    <label>
                        <input type="checkbox" checked={enter2Paragraph} onChange={this._onEnter2ParagraphChange} />
                        Enter key inserts a paragraph (&para;)
                    </label>
                </div>
                <small>
                    {' Supports Markdown. '}
                    <a href={LearnMoreMarkdownUrl} target="_blank" rel="noreferrer">
                        Read more (opens a new window)
                    </a>.
                </small>
            </div>
        </>;
    }

    private _setMarkdownEditorRef = (component: HTMLTextAreaElement) => {
        this._markdownEditor = component;
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
        const start = target.selectionStart;
        const end = target.selectionEnd;

        // TODO: #24 Do not trigger this behavior when the user is working on a markdown component, such as a list or table.
        if (ev.key === 'Enter' && start !== undefined) {
            // Intercept the event and prevent propagation as the default behavior for the enter key is completely
            // overridden by this component.
            ev.preventDefault();
            ev.stopPropagation();

            // Insert a paragraph between the start and end position of the current 'selection' (i.e. caret position).
            const newValue = [
                target.value.substr(0, start),
                ParagraphDelimiter,
                target.value.substr(end),
            ].join('');

            // The component needs to remember the last caret position so that it can be restored after the
            // component's value prop is changed. If the component do not remember the caret position, the
            // caret will be moved to the end of the string.
            this.setState({
                caretPosition: end + ParagraphDelimiter.length,
            });

            // Inform observers that the value has changed. It is necessary to manually fire the change event
            // because the event has been intercepted and propagation has been disabled.
            this._triggerChange(newValue);
        }
    }
}
