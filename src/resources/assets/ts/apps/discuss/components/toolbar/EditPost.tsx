import {
    useCallback,
    useState,
} from 'react';

import { fireEventAsync } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import { withPropResolving } from '@root/di';
import { DI } from '@root/di/keys';

import Form from '../Form';
import { IFormChangeData } from '../Form._types';
import ActionLink from './ActionLink';
import { IProps } from './index._types';

function EditPost(props: IProps) {
    const {
        apiConnector,
        onPostChange,
        post,
        thread,
        threadMetadata,
    } = props;
    const forumPostId = post.id;

    const [ isOpen, setIsOpen ] = useState(false);
    const [ content, setContent ] = useState(null);
    const [ subject, setSubject ] = useState(null);

    const _onOpenDialog = useCallback(async () => {
        try {
            const postData = await apiConnector.post({
                forumPostId,
                includeDeleted: false,
                markdown: true,
            });

            setContent(postData.post.content);
            setSubject(postData.post.forumThread.subject);
            setIsOpen(true);
        } catch (e) {
            // TODO handle failure
        }
    }, [ apiConnector, forumPostId, setContent, setIsOpen, setSubject ]);

    const _onDismissDialog = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const _onFormChange = useCallback((ev: IComponentEvent<IFormChangeData>) => {
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
                console.warn(`EditPost._onFormChange: Unrecognised signal ${name}.`);
                break;
        }

    }, [ setContent, setSubject ]);

    const _onSubmit = useCallback(async () => {
        try {
            await apiConnector.updatePost({
                content,
                forumPostId,
                subject,
            });

            fireEventAsync(`EditPost[${forumPostId}]`, onPostChange, forumPostId);
            setIsOpen(false);
        } catch (e) {
            // TODO handle failure
        }
    }, [
        apiConnector, forumPostId, onPostChange, setIsOpen,
        content, subject,
    ]);

    return <>
        <ActionLink icon="edit"
            onClick={_onOpenDialog}
            post={post}
            thread={thread}
            threadMetadata={threadMetadata}>
            Edit
        </ActionLink>
        <Dialog<number>
            actionBar={false}
            onDismiss={_onDismissDialog}
            open={isOpen}
            title="Edit post"
            size="xl"
            value={0}>
            <Form content={content}
                name={`edit-post-${post.id}`}
                onCancel={_onDismissDialog}
                onChange={_onFormChange}
                onSubmit={_onSubmit}
                subjectEnabled={post._isThreadPost === true}
                subject={subject}
            />
        </Dialog>
    </>;
}

export default withPropResolving(EditPost, {
    apiConnector: DI.DiscussApi,
});

