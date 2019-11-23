import React from 'react';

import {
    IRoleManager,
    SecurityRole,
} from '@root/security';

import { IProps } from './index._types';

import DeletePost from './DeletePost';
import EditPost from './EditPost';
import Likes from './Likes';

import './index.scss';

const getEligibleToolbarComponents = (roleManager: IRoleManager, postAccountId: number) => {
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
    const {
        post,
        roleManager,
    } = props;
    const toolbar = getEligibleToolbarComponents(roleManager, post.account.id);

    return <span className="post-header--tools">
        {toolbar.map((Component, i) => <Component key={i} {...props} />)}
    </span>;
}

export default Toolbar;
