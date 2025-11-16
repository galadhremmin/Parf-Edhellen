import {
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';
import type { ChangeEvent, KeyboardEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import Markdown from '@root/components/Markdown';
import { LearnMoreMarkdownUrl } from '@root/config';
import debounce from '@root/utilities/func/debounce';
import type { IProps } from './EditTabView._types';

import { isEmptyString } from '@root/utilities/func/string-manipulation';
import './EditTabView.scss';

const LineDelimiter = `\n`;
const ParagraphDelimiter = `\n\n`;

function EditTabView(props: IProps) {
    const {
        enter2Paragraph,
        id,
        markdownApi,
        name,
        required,
        rows,
        value,

        onChange,
        onEnter2ParagraphChange,
    } = props;

    const markdownEditorRef = useRef<HTMLTextAreaElement>(null);
    const [ initialized, setInitialized ] = useState<boolean>(false);
    const [ caretPosition, setCaretPosition ] = useState<number | null>(null);
    const [ previewValue, setPreviewValue ] = useState<string>(value);

    useEffect(() => {
        if (caretPosition !== null && markdownEditorRef.current !== null) {
            // Set the caret position to the one held in component state. This is a one-time
            // operation used to recover caret position for when paragraphs (or other textarea
            // events are overridden).
            requestIdleCallback(() => {
                markdownEditorRef.current.selectionEnd = caretPosition;
                setCaretPosition(null);
            });
        }
    }, [ markdownEditorRef, caretPosition ]);

    useEffect(() => {
        if (! initialized && ! isEmptyString(value)) {
            setInitialized(true);
            setPreviewValue(value);
        }
    }, [initialized, value]);
    
    const _triggerPreview = useCallback(
        debounce(500, (value: string) => {
            setPreviewValue(value);
        }),
    []);

    const _triggerChange = useCallback((newValue: string) => {
        void fireEvent(name, onChange, newValue);
        _triggerPreview(newValue);
    }, [ name, onChange ]);

    const _onEnter2ParagraphChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        void fireEvent(name, onEnter2ParagraphChange, ev.target.checked);
    }, [ name, onEnter2ParagraphChange ]);

    const _onMarkdownChange = useCallback((ev: ChangeEvent<HTMLTextAreaElement>) => {
        _triggerChange(ev.target.value);
    }, []);

    const _onMarkdownKeyDown = useCallback((ev: KeyboardEvent<HTMLTextAreaElement>) => {
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

            const stringUntilCaret = target.value.substring(0, start);
            const lastNewLineBeforeCaret = stringUntilCaret.lastIndexOf(LineDelimiter);
            const currentLine = target.value.substring(lastNewLineBeforeCaret + 1, start).trim();
            let insert = ParagraphDelimiter;
            if (lastNewLineBeforeCaret > -1 && lastNewLineBeforeCaret < start && 
                // New line starting with * (unordered list), \d. (ordered list) or | (table) OR when the line is empty
                (/^\s*([*|]{1}|\d+\.\s)/.test(currentLine) || currentLine.length < 1)
            ) {
                insert = LineDelimiter;
            }

            // Insert a paragraph between the start and end position of the current 'selection' (i.e. caret position).
            const newValue = [
                stringUntilCaret,
                insert,
                target.value.substring(end),
            ].join('');

            // The component needs to remember the last caret position so that it can be restored after the
            // component's value prop is changed. If the component do not remember the caret position, the
            // caret will be moved to the end of the string.
            setCaretPosition(end + insert.length);

            // Inform observers that the value has changed. It is necessary to manually fire the change event
            // because the event has been intercepted and propagation has been disabled.
            _triggerChange(newValue);
        }
    }, [ enter2Paragraph ]);

    return <>
        <div className="EditTabView--split-view">
            <div className="EditTabView--split-view__input">
                <textarea className="form-control"
                        id={id}
                        name={name}
                        onChange={_onMarkdownChange}
                        onKeyDown={_onMarkdownKeyDown}
                        required={required}
                        rows={rows}
                        value={value}
                        ref={markdownEditorRef}
                        placeholder="Click or tap here to start typing."
                />
            </div>
            <div className="EditTabView--split-view__preview">
                <Markdown parse={true} text={previewValue} markdownApi={markdownApi} />
            </div>
        </div>
        <div className="text-end">
            <div className="checkbox">
                <label>
                    <input type="checkbox" checked={enter2Paragraph} onChange={_onEnter2ParagraphChange} />
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

export default EditTabView;
