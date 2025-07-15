import {
  useCallback,
  useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import { ICreatePostRequest } from '@root/connectors/backend/IDiscussApi';
import ValidationError from '@root/connectors/ValidationError';
import { resolve, withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import {
  SecurityRole,
} from '@root/security';

import CreateThreadButton from '../components/CreateThreadButton';
import FiltersButton from '../components/FiltersButton';
import { IProps } from '../index._types';

import './Toolbar.scss';

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

            const browserHistory = resolve(DI.BrowserHistory);
            browserHistory.redirect(postData.postUrl);
        } catch (e) {
            if (e instanceof ValidationError) {
                setError(e);
            } else {
                throw e;
            }
        }
    }, [ apiConnector, setError ]);

    return <div className="DiscussToolbar text-center">
        <FiltersButton />
        <CreateThreadButton error={error}
                            enabled={roleManager.hasRole(SecurityRole.Discuss)}
                            groupId={props.groupId}
                            groupName={props.groupName}
                            onThreadCreate={_onThreadCreate} />
    </div>;
}

export default withPropInjection(Toolbar, {
    apiConnector: DI.DiscussApi,
    roleManager: DI.RoleManager,
});
