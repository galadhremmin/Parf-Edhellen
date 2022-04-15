import React, { useCallback, useState } from 'react';

import { fireEventAsync } from '@root/components/Component';
import Dialog from '@root/components/Dialog';
import TextIcon from '@root/components/TextIcon';
import { IForumGroupEntity } from '@root/connectors/backend/IDiscussApi';

import connectApi from './ApiConnector';
import { IProps } from './ApiConnector._types';
import './Likes.scss';
import StaticAlert from '@root/components/StaticAlert';
import Quote from '@root/components/Quote';

function MovePost(props: IProps) {
    const {
        apiConnector,
        thread,
    } = props;
    const {
        id: threadId,
        forumGroupId: currentGroupId,
        subject,
    } = props.thread;

    const [ isDialogOpen, setIsDialogOpen ] = useState<boolean>(false);
    const [ groups, setGroups ] = useState<Record<string, IForumGroupEntity[]>>({});
    const [ groupId, setGroupId ] = useState<number>(currentGroupId || 0);
    const [ error, setError ] = useState<string>(null);

    const _onDialogOpen = useCallback(async () => {
        if (! isDialogOpen) {
            try {
                const nextGroups = await apiConnector.groups();
                setGroups(nextGroups.groups);
            } catch (ex) {
                setError(`Failed to load discuss groups. Error description: ${ex}`);
                setGroups({});
            }
        }

        setIsDialogOpen(x => !x);
    }, [
        isDialogOpen,
        apiConnector,
    ]);

    const _onForumGroupChange = useCallback((ev: React.ChangeEvent<HTMLSelectElement>) => {
        const nextGroupId = parseInt(ev.target.value, 10);
        setGroupId(nextGroupId);
    }, []);

    const _onMoveSubmit = useCallback(async () => {
        try {
            if (currentGroupId === groupId) {
                throw new Error(`No movement necessary, the thread is already assigned to the group you've selected.`);
            }

            apiConnector.moveThread({
                forumGroupId: groupId,
                forumThreadId: threadId,
            });
            setError(null);
            alert(`The thread has been successfully moved. The change will reflect next time you load the thread.`);
            setIsDialogOpen(false);
        } catch (ex) {
            setError(`Failed to move the thread. ${ex}`);
        }
    }, [
        apiConnector,
        currentGroupId,
        threadId,
        groups,
        groupId,
    ]);

    return <>
        <a href="#" onClick={_onDialogOpen}>
            <TextIcon icon="move" />{' Move'}
        </a>
        <Dialog<number>
            confirmButtonText="Move"
            open={isDialogOpen}
            title={<>Move <Quote>{subject}</Quote></>}
            onDismiss={_onDialogOpen}
            onConfirm={_onMoveSubmit}>
            {error && <StaticAlert type="danger">{error}</StaticAlert>}
            <label htmlFor="ed-discuss--forum-group-id">Discuss group:</label>
            <select className="form-select" id="ed-discuss--forum-group-id"
                onChange={_onForumGroupChange}
                value={groupId}>
                {Object.keys(groups).map(groupName => <optgroup key={groupName} label={groupName}>
                    {groups[groupName].map((group) => <option
                        key={group.id}
                        value={group.id}>
                        {group.name}
                    </option>)}
                </optgroup>)}
            </select>
        </Dialog>
    </>;
}

export default connectApi(MovePost);
