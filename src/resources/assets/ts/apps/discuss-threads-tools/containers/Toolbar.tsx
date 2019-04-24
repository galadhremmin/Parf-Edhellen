import React, {
    useCallback,
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import { ICreatePostRequest } from '@root/connectors/backend/DiscussApiConnector._types';
import ValidationError from '@root/connectors/ValidationError';
import {
    RoleManager,
    SecurityRole,
} from '@root/security';
import BrowserHistory from '@root/utilities/BrowserHistory';
import SharedReference from '@root/utilities/SharedReference';

import CreateThreadButton from '../components/CreateThreadButton';
import { IProps } from '../index._types';

function Toolbar(props: IProps) {
    const {
        apiConnector,
        roleManager,
    } = props;

    const [ error, setError ] = useState<ValidationError>(null);

    const _onThreadCreate = useCallback(async (ev: IComponentEvent<ICreatePostRequest>) => {
        try {
            const postData = await apiConnector.createPost(ev.value);
            setError(null);

            const browserHistory = SharedReference.getInstance(BrowserHistory);
            browserHistory.redirect(postData.postUrl);
        } catch (e) {
            if (e instanceof ValidationError) {
                setError(e);
            } else {
                throw e;
            }
        }
    }, [ apiConnector, setError ]);

    return <div className="text-right">
        <CreateThreadButton error={error}
                            enabled={roleManager.currentRole !== SecurityRole.Anonymous}
                            groupId={props.groupId}
                            groupName={props.groupName}
                            onThreadCreate={_onThreadCreate} />
    </div>;
}

Toolbar.defaultProps = {
    apiConnector: SharedReference.getInstance(DiscussApiConnector),
    roleManager: SharedReference.getInstance(RoleManager)
} as Partial<IProps>;

export default Toolbar;
