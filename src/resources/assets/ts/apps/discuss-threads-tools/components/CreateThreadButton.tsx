import React, {
    useCallback,
    useState,
} from 'react';

import Form from '@root/apps/discuss/components/Form';
import {
    IFormChangeData,
    IFormOutput,
} from '@root/apps/discuss/components/Form._types';
import UnauthenticatedAlert from '@root/apps/discuss/components/UnauthenticatedAlert';
import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import { IProps } from './CreateThreadButton._types';

function CreateThreadButton(props: IProps) {
    const {
        error,
        enabled,
        groupId,
        groupName,
        onThreadCreate,
    } = props;

    const [ isOpen, setIsOpen ] = useState(false);
    const [ content, setContent ] = useState('');
    const [ subject, setSubject ] = useState('');

    const _onOpen = useCallback(() => {
        setIsOpen(true);
    }, [ setIsOpen ]);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const _onChange = useCallback((ev: IComponentEvent<IFormChangeData>) => {
        const {
            name,
            value,
        } = ev.value;

        switch (name) {
            case 'content':
                setContent(value);
                break;
            case 'subject':
                setSubject(value);
                break;
            default:
                console.warn(`Unrecognised component ${name}.`);
        }
    }, [ setContent, setSubject ]);

    const _onSubmit = useCallback((ev: IComponentEvent<IFormOutput>) => {
        fireEvent(`CreateThreadButton[${groupId}]`, onThreadCreate, {
            ...ev.value,
            entityType: 'discussion',
            forumGroupId: groupId,
            isSticky: false,
        });
    }, [ groupId, onThreadCreate, setIsOpen ]);

    return <>
        <button className="btn btn-primary" onClick={_onOpen}>
            <span className="glyphicon glyphicon-pencil" />
            {' '}
            New post
        </button>
        <Dialog actionBar={false}
                open={isOpen}
                onDismiss={_onDismiss}
                title={`Create thread in ${groupName}`}>
            <ValidationErrorAlert error={error} />
            {enabled
                ? <Form content={content}
                        name="discuss-form-new-group"
                        subject={subject}
                        onCancel={_onDismiss}
                        onChange={_onChange}
                        onSubmit={_onSubmit}
                  /> : <UnauthenticatedAlert />}
        </Dialog>
    </>;
}

export default CreateThreadButton;
