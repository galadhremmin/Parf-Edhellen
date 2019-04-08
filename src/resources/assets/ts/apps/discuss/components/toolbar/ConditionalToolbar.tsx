import React from 'react';

import Spinner from '@root/components/Spinner';
import {
    RoleManager,
    SecurityRole,
} from '@root/security';
import SharedReference from '@root/utilities/SharedReference';

import { IProps } from './ConditionalToolbar._types';

export default class ConditionalToolbar extends React.Component<IProps> {
    public static defaultProps = {
        roleManager: SharedReference.getInstance(RoleManager),
    } as Partial<IProps>;

    public render() {
        const props = this.props;

        if (props.roleManager.currentRole === SecurityRole.Anonymous) {
            return null;
        }

        return <React.Suspense fallback={<Spinner />}>
            <ToolbarAsync {...props} />
        </React.Suspense>;
    }
}

const ToolbarAsync = React.lazy(() => import('.'));
