import React, { useCallback, useRef } from 'react';

import { fireEvent } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import { DI, resolve } from '@root/di';
import {
    RoleManager,
    SecurityRole,
} from '@root/security';
import { IProps } from './RespondButton._types';
import UnauthenticatedAlert from './UnauthenticatedAlert';

function RespondButton(props: IProps) {
    const {
        isNewPost,
        onClick,
    } = props;

    // This is the singleton pattern for hooks from React's documentation
    // https://reactjs.org/docs/hooks-faq.html#how-to-create-expensive-objects-lazily
    const roleManagerRef = useRef<RoleManager>(null);
    const getRoleManager = () => {
        let roleManager = roleManagerRef.current;
        if (roleManager !== null) {
            return roleManager;
        }

        roleManager = resolve(DI.RoleManager);
        roleManagerRef.current = roleManager;
        return roleManager;
    };

    const onRespondClick = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent(null, onClick);
    }, [ onClick ]);

    switch (getRoleManager().currentRole) {
        case SecurityRole.Anonymous:
            return <UnauthenticatedAlert />;

        default:
            return <button className="btn btn-primary" onClick={onRespondClick}>
                <TextIcon icon="envelope" />
                &nbsp;
                {isNewPost ? 'Create thread' : 'Reply'}
            </button>;
    }
}

export default RespondButton;
