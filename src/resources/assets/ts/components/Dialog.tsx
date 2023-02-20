import React, {
    useCallback, useEffect,
} from 'react';
import Modal from 'react-modal';

import { fireEvent } from './Component';
import { IProps } from './Dialog._types';

import './Dialog.scss';
import classNames from 'classnames';

function Dialog<V>(props: IProps<V>) {
    const {
        actionBar,
        cancelButtonText,
        children,
        confirmButtonText,
        dismissable,
        open,
        onConfirm,
        onDismiss,
        size,
        title,
        valid,
        value,
    } = props;

    const _onDismissDialog = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        fireEvent('Dialog', onDismiss);
    }, [ onDismiss ]);

    const _onConfirm = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        if (valid) {
            fireEvent('Dialog', onConfirm, value);
        }
    }, [ onConfirm, valid, value ]);

    useEffect(() => {
        const noscrollClass = 'DialogBody--noscroll';
        if (open) {
            document.body.classList.add(noscrollClass);
        } else {
            document.body.classList.remove(noscrollClass);
        }

        return () => {
            if (open) {
                document.body.classList.remove(noscrollClass);
            }
        };
    }, [ open ]);

     // This is needed so screen readers don't see main content when modal is opened.
     const appElement = document.querySelector('main');
    if (! open) {
        // This is an optimization that is intended to keep the Dialog component mounted
        // but that pesky `ReactModalPortal` element NOT in the DOM.
        return null;
    }
    return <Modal appElement={appElement}
        className="modal"
        isOpen={open}
        style={DialogStyles}>
        <div className={classNames('modal-dialog', { [`modal-${size}`]: !! size })}>
            <div className="modal-content">
                <div className="modal-header">
                    <h4 className="modal-title">{title}</h4>
                    {dismissable && <button type="button" className="btn-close" aria-label="Close" onClick={_onDismissDialog} />}
                </div>
                <div className="modal-body">
                    {children}
                </div>
                {actionBar && <div className="modal-footer Dialog--footer">
                    {dismissable && <button type="button" className="btn btn-light" onClick={_onDismissDialog}>
                        {cancelButtonText}
                    </button>}
                    {onConfirm && <button type="button" className="btn btn-primary" onClick={_onConfirm}>
                        {confirmButtonText}
                    </button>}
                </div>}
            </div>
        </div>
    </Modal>;
}

Dialog.defaultProps = {
    actionBar: true,
    cancelButtonText: 'Close',
    confirmButtonText: 'OK',
    dismissable: true,
    valid: true,
} as Partial<IProps<any>>;

const DialogStyles = {
    content: {
        display: 'inherit',
        left: '50%',
        marginRight: '-50%',
        right: 'auto',
        transform: 'translate(-50%, 0%)',
    },
    overlay: {
        zIndex: 1000,
    },
};

export default Dialog;
