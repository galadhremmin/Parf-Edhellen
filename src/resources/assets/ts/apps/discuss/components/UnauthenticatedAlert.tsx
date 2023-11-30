import React from 'react';
import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';

const onAuthenticateClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
    ev.preventDefault();
    window.location.href = `/login?redirect=${encodeURIComponent(window.location.pathname)}`;
};

function UnauthenticatedAlert() {
    return <StaticAlert type="info">
        <strong>
            <TextIcon icon="info-sign" />
            {' '}
            Would you like to share your thoughts on the discussion?
        </strong>
        {' '}
        <a href="#" onClick={onAuthenticateClick}>
            Sign in and create a profile
        </a>.
    </StaticAlert>;
}

export default UnauthenticatedAlert;
