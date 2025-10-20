import classNames from 'classnames';
import { useCallback } from 'react';

import { fireEvent } from './Component';
import { IProps } from './StaticAlert._types';

function StaticAlert(props: IProps) {
    const {
        children,
        className = '',
        dismissable,
        onDismiss,
        type = 'info',
    } = props;

    const _onDismiss = useCallback(() => {
        void fireEvent('StaticAlert', onDismiss);
    }, [onDismiss]);

    return <div className={classNames(`alert alert-${type} bg-gradient`, { 'alert-dismissible': dismissable }, className)} role="alert">
        {dismissable && <button type="button" className="btn-close" aria-label="Close" onClick={_onDismiss} />}
        {children}
    </div>;
}

export default StaticAlert;
