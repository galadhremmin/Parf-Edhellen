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

    return <div className={classNames(`alert alert-${type}`, { 'alert-dismissible': dismissable })}>
        {dismissable && <button type="button" className="close" aria-label="Close" onClick={_onDismiss}>
            <span aria-hidden="true">&times;</span>
        </button>}
        {children}
    </div>;
};

StaticAlert.defaultProps = {
    type: 'info',
};

export default StaticAlert;
