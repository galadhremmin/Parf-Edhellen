import React from 'react';

import {
    RoleManager,
    SecurityRole,
} from '@root/security';
import SharedReference from '@root/utilities/SharedReference';

import DeletePost from '../components/DeletePost';
import EditPost from '../components/EditPost';
import { IProps } from './Toolbar._types';

export default class Toolbar extends React.PureComponent<IProps> {
    private _roleManager = new SharedReference(RoleManager);

    public render() {
        const {
            accountId,
            postId,
        } = this.props;
        const toolbar = this._getEligibleToolbarComponents(accountId);

        return <React.Fragment>
            {toolbar.map((Component, i) => <Component key={i}
                accountId={accountId}
                postId={postId}
            />)}
        </React.Fragment>;
    }

    private _getEligibleToolbarComponents(postAccountId: number) {
        const role = this._roleManager.value.currentRole;
        const accountId = this._roleManager.value.accountId;
        const components = [];

        if (role === SecurityRole.Administrator ||
            accountId === postAccountId) {
            components.push(EditPost);
            components.push(DeletePost);
        }

        return components;
    }
}
