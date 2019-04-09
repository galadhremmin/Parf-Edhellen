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

import './index.scss';

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

    return <span className="post-header--tools">
        {toolbar.map((Component, i) => <Component key={i} {...props} />)}
    </span>;
}

export default Toolbar;
