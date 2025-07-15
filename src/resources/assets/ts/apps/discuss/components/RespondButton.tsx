import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import { SecurityRole } from '@root/security';

import { IProps } from './RespondButton._types';
import UnauthenticatedAlert from './UnauthenticatedAlert';
import ValidateEmailAlert from './ValidateEmailAlert';

function RespondButton(props: IProps) {
    const {
        isNewPost,
        onClick,
        roleManager,
    } = props;

    const onRespondClick = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent(null, onClick);
    }, [ onClick ]);

    if (roleManager.isAnonymous) {
        return <UnauthenticatedAlert />;
    }

    if (! roleManager.hasRole(SecurityRole.Discuss)) {
        return <ValidateEmailAlert />;
    }

    return <button className="btn btn-primary" onClick={onRespondClick}>
        <TextIcon icon="envelope" />
        &nbsp;
        {isNewPost ? 'Create thread' : 'Reply'}
    </button>;
}

export default withPropInjection(RespondButton, {
    roleManager: DI.RoleManager,
});
