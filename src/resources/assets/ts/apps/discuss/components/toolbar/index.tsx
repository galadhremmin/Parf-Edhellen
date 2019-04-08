import React from 'react';

import {
    RoleManager,
    SecurityRole,
} from '@root/security';
import SharedReference from '@root/utilities/SharedReference';

import { IProps } from './index._types';

import DeletePost from './DeletePost';
import EditPost from './EditPost';
import Likes from './Likes';

const getEligibleToolbarComponents = (postAccountId: number) => {
    const roleManager = SharedReference.getInstance(RoleManager);
    const role = roleManager.currentRole;
    const accountId = roleManager.accountId;
    const components = [];

    if (role === SecurityRole.Administrator ||
        accountId === postAccountId) {
        components.push(EditPost);
        components.push(DeletePost);
    }

    components.push(Likes);
    return components;
};

function Toolbar(props: IProps) {
    const toolbar = getEligibleToolbarComponents(props.post.account.id);

    return <React.Fragment>
        {toolbar.map((Component, i) => <Component key={i} {...props} />)}
    </React.Fragment>;
}

export default Toolbar;
