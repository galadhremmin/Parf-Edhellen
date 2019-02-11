import React, { useCallback, useRef } from 'react';

import { fireEvent } from '@root/components/Component';
import {
    RoleManager,
    SecurityRole,
} from '@root/security';
import SharedReference from '@root/utilities/SharedReference';
import { IProps } from './RespondButton._types';

const onAuthenticateClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
};

function RespondButton(props: IProps) {
    const {
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

        roleManager = SharedReference.getInstance(RoleManager);
        roleManagerRef.current = roleManager;
        return roleManager;
    };

    const onRespondClick = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent(null, onClick);
    }, [ onClick ]);

    switch (getRoleManager().currentRole) {
        case SecurityRole.Anonymous:
            return <div className="alert alert-info" id="forum-log-in-box">
                <strong>
                    <span className="glyphicon glyphicon-info-sign" />
                    {' Would you like to share your thoughts on the discussion?'}
                </strong>
                {' '}
                <a href="#" onClick={onAuthenticateClick}>
                    Log in to create a profile
                </a>.
            </div>;

        default:
            return <button className="btn btn-primary" onClick={onRespondClick}>Reply</button>;
    }
}

export default RespondButton;
