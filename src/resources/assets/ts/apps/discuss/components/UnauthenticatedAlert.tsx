import React from 'react';

import StaticAlert from '@root/components/StaticAlert';

const onAuthenticateClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
};

function UnauthenticatedAlert() {
    return <StaticAlert type="info">
        <strong>
            <span className="glyphicon glyphicon-info-sign" />
            {' '}
            Would you like to share your thoughts on the discussion?
        </strong>
        {' '}
        <a href="#" onClick={onAuthenticateClick}>
            Log in to create a profile
        </a>.
    </StaticAlert>;
}

export default UnauthenticatedAlert;
