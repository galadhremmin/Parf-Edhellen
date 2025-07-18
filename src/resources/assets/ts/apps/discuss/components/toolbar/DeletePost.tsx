import {
  useCallback,
  useState,
} from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import DateLabel from '@root/components/DateLabel';
import Dialog from '@root/components/Dialog';
import ProfileLink from '@root/components/ProfileLink';
import { withPropInjection } from '@root/di';

import { DI } from '@root/di/keys';
import ActionLink from './ActionLink';
import { IProps } from './index._types';

function DeletePost(props: IProps) {
    const [ needsConfirmation, setNeedsConfirmation ] = useState(false);

    const {
        apiConnector,
        onPostChange,
        post,
        threadMetadata,
    } = props;

    const _onDelete = useCallback(() => {
        setNeedsConfirmation(true);
    }, [ setNeedsConfirmation ]);

    const _onConfirmDismiss = useCallback(() => {
        setNeedsConfirmation(false);
    }, [ setNeedsConfirmation ]);

    const _onDeleteConfirmed = useCallback(async (ev: IComponentEvent<number>) => {
        const forumPostId = ev.value;

        try {
            await apiConnector.deletePost({
                forumPostId,
            });

            void fireEvent(`DeletePost[${forumPostId}]`, onPostChange, forumPostId);
        } catch (e) {
            // TODO: handle failure
        }
    }, [ apiConnector, onPostChange ]);

    return <>
        <ActionLink icon="trash"
            post={post}
            thread={props.thread}
            threadMetadata={threadMetadata}
            onClick={_onDelete}>
            Delete
        </ActionLink>
        <Dialog<number>
            onDismiss={_onConfirmDismiss}
            open={needsConfirmation}
            title="Confirm deletion"
            onConfirm={_onDeleteConfirmed}
            value={post.id}>
            <p>
                Post ID {post.id} was authored by <strong><ProfileLink account={post.account} /></strong>{' '}
                on <strong><DateLabel dateTime={post.createdAt} /></strong>{' '}
                and has earned <strong>{threadMetadata.likesPerPost[post.id]}</strong> thumbs up.
            </p>
            <p>Are you sure you want to proceed with the deletion?</p>
        </Dialog>
    </>;
}

export default withPropInjection(DeletePost, {
    apiConnector: DI.DiscussApi,
});
