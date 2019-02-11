import React, {
    useCallback,
    useState,
} from 'react';

import { fireEventAsync } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import MarkdownInput from '@root/components/MarkdownInput';

import {
    IProps,
} from './Form._types';

function Form(props: IProps) {
    const [ content, setContent ] = useState(() => props.content);
    const [ subject, setSubject ] = useState(() => props.subject);

    const {
        subjectEnabled,
        onCancel,
        onSubmit,
    } = props;

    const onContentChange = useCallback(
        (e: IComponentEvent<string>) => setContent(e.value),
        [ setContent ]);
    const onSubjectChange = useCallback(
        (e: React.ChangeEvent<HTMLInputElement>) => setSubject(e.target.value),
        [ setSubject ]);
    const onCancelClick = useCallback((e: React.MouseEvent) => {
        e.preventDefault();
        fireEventAsync(null, onCancel);
    }, [ onCancel ]);
    const onSubmitForm = useCallback((ev: React.FormEvent) => {
        ev.preventDefault();
        fireEventAsync(null, onSubmit);
    }, [ onSubmit ]);

    return <form method="get" action="/#intercepted-action" onSubmit={onSubmitForm}>
        {subjectEnabled && <div className="form-group">
            <label htmlFor="ed-discuss-subject" className="control-label">Subject</label>
            <input type="text"
                className="form-control"
                id="ed-discuss-subject"
                name="subject"
                value={subject}
                onChange={onSubjectChange}
            />
        </div>}
        <div className="form-group">
            <label htmlFor="ed-discuss-content" className="control-label">Message</label>
            <MarkdownInput id="ed-discuss-content"
                        className="form-control"
                        name="content"
                        rows={8}
                        value={content}
                        onChange={onContentChange}
            />
        </div>
        <div className="form-group text-right">
            <button className="btn btn-default" onClick={onCancelClick}>Cancel</button>
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
