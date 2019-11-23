import React, { Suspense } from 'react';

import Spinner from '@root/components/Spinner';
import { DI, resolve } from '@root/di';
import {
    SecurityRole,
} from '@root/security';

import { IProps } from './ConditionalToolbar._types';

export default class ConditionalToolbar extends React.Component<IProps> {
    public static defaultProps = {
        roleManager: resolve(DI.RoleManager),
    } as Partial<IProps>;

    public render() {
        const props = this.props;

        if (props.roleManager.currentRole === SecurityRole.Anonymous) {
            return null;
        }

        return <Suspense fallback={<Spinner />}>
            <ToolbarAsync {...props} />
        </Suspense>;
    }
}

const ToolbarAsync = React.lazy(() => import('.'));
