import React, {
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import MarkdownInput from '@root/components/MarkdownInput';

import {
    IProps,
} from './Form._types';

function Form(props: IProps) {
    const [ content, setContent ] = useState(() => props.content);
    const [ subject, setSubject ] = useState(() => props.subject);

    const updateContent = (e: IComponentEvent<string>) => setContent(e.value);
    const updateSubject = (e: React.ChangeEvent<HTMLInputElement>) => setSubject(e.target.value);

    const {
        subjectEnabled,
    } = props;

    return <form method="post" action="/dashboard/discuss">
        {subjectEnabled && <div className="form-group">
            <label htmlFor="ed-discuss-subject" className="control-label">Subject</label>
            <input type="text"
                className="form-control"
                id="ed-discuss-subject"
                name="subject"
                value={subject}
                onChange={updateSubject}
            />
        </div>}
        <div className="form-group">
            <label htmlFor="ed-discuss-content" className="control-label">Message</label>
            <MarkdownInput id="ed-discuss-content"
                        className="form-control"
                        name="content"
                        rows={8}
                        value={content}
                        onChange={updateContent}
            />
        </div>
        <div className="form-group text-right">
            <a href="/discuss" className="btn btn-default">Cancel</a>
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
} as Partial<IProps>;

export default Form;
