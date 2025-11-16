import {
    useCallback,
} from 'react';
import type { ChangeEvent, MouseEvent, FormEvent } from 'react';

import {
    fireEvent,
    fireEventAsync,
} from '@root/components/Component';
import type { IComponentEvent } from '@root/components/Component._types';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import TextIcon from '@root/components/TextIcon';

import type {
    IFormOutput,
    IProps,
} from './Form._types';

function Form(props: IProps) {
    const {
        content = '',
        name,
        onCancel = null,
        onChange = null,
        onSubmit = null,
        subject = '',
        subjectEnabled = true,
    } = props;

    const _onContentChange = useCallback(
        (e: IComponentEvent<string>) => {
            void fireEvent(name, onChange, e);
        },
        [ name, onChange ]);

    const _onSubjectChange = useCallback(
        (e: ChangeEvent<HTMLInputElement>) => {
            void fireEvent(name, onChange, {
                name: 'subject',
                value: e.target.value,
            });
        }, [ name, onChange ]);

    const _onCancelClick = useCallback((e: MouseEvent) => {
        e.preventDefault();
        void fireEventAsync(null, onCancel);
    }, [ onCancel ]);

    const _onSubmitForm = useCallback((ev: FormEvent) => {
        ev.preventDefault();
        const args = {
            content,
            subject,
        } as IFormOutput;
        void fireEventAsync(name, onSubmit, args);
    }, [ content, name, onSubmit, subject ]);

    return <form method="get" action="/#intercepted-action" onSubmit={_onSubmitForm}>
        {subjectEnabled && <div className="form-group">
            <label htmlFor="ed-discuss-subject" className="control-label">Subject</label>
            <input type="text"
                className="form-control"
                id="ed-discuss-subject"
                name="subject"
                required={true}
                value={subject}
                onChange={_onSubjectChange}
            />
        </div>}
        <div className="form-group">
            <label htmlFor="ed-discuss-content" className="control-label">Message</label>
            <MarkdownInput id="ed-discuss-content"
                className="form-control"
                name="content"
                onChange={_onContentChange}
                required={true}
                rows={8}
                value={content}
            />
        </div>
        <div className="text-center">
            <div className="btn-group">
                <button className="btn btn-secondary" onClick={_onCancelClick}>Cancel</button>
                <button type="submit" className="btn btn-primary">
                    <TextIcon icon="edit" />
                    Save
                </button>
            </div>
        </div>
    </form>;
}

export default Form;
