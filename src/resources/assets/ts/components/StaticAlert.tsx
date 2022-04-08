import React, { useCallback } from 'react';
import classNames from 'classnames';

import { fireEvent } from './Component';
import { IProps } from './StaticAlert._types';

function StaticAlert(props: IProps) {
    const {
        children,
        dismissable,
        onDismiss,
        type,
    } = props;

    const _onDismiss = useCallback(() => {
        fireEvent('StaticAlert', onDismiss);
    }, [onDismiss]);

    return <div className={classNames(`alert alert-${type} bg-gradient`, { 'alert-dismissible': dismissable })} role="alert">
        {dismissable && <button type="button" className="btn-close" aria-label="Close" onClick={_onDismiss} />}
        {children}
    </div>;
};

StaticAlert.defaultProps = {
    type: 'info',
};

export default StaticAlert;
