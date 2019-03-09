import React, {
    useCallback,
} from 'react';

import {
    fireEvent,
    fireEventAsync,
} from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import MarkdownInput from '@root/components/MarkdownInput';

import {
    IFormOutput,
    IProps,
} from './Form._types';

function Form(props: IProps) {
    const {
        content,
        name,
        onCancel,
        onChange,
        onSubmit,
        subject,
        subjectEnabled,
    } = props;

    const _onContentChange = useCallback(
        (e: IComponentEvent<string>) => fireEvent(name, onChange, e),
        [ name, onChange ]);

    const _onSubjectChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => fireEvent(name, onChange, {
            name,
            value: e.target.value,
        }), [ name, onChange ]);

    const _onCancelClick = useCallback((e: React.MouseEvent) => {
        e.preventDefault();
        fireEventAsync(null, onCancel);
    }, [ onCancel ]);

    const _onSubmitForm = useCallback((ev: React.FormEvent) => {
        ev.preventDefault();
        const args = {
            content,
            subject,
        } as IFormOutput;
        fireEventAsync(name, onSubmit, args);
    }, [ content, name, onSubmit, subject ]);

    return <form method="get" action="/#intercepted-action" onSubmit={_onSubmitForm}>
        {subjectEnabled && <div className="form-group">
            <label htmlFor="ed-discuss-subject" className="control-label">Subject</label>
            <input type="text"
                className="form-control"
                id="ed-discuss-subject"
                name="subject"
                value={subject}
                onChange={_onSubjectChange}
            />
        </div>}
        <div className="form-group">
            <label htmlFor="ed-discuss-content" className="control-label">Message</label>
            <MarkdownInput id="ed-discuss-content"
                        className="form-control"
                        name="content"
                        rows={8}
                        value={content}
                        onChange={_onContentChange}
            />
        </div>
        <div className="form-group text-right">
            <button className="btn btn-default" onClick={_onCancelClick}>Cancel</button>
            <button type="submit" className="btn btn-primary">
                <span className="glyphicon glyphicon-pencil"></span>
                Save
            </button>
        </div>
    </form>;
}

Form.defaultProps = {
    content: '',
    subject: '',
    subjectEnabled: true,

    onCancel: null,
    onSubmit: null,
} as Partial<IProps>;

export default Form;
