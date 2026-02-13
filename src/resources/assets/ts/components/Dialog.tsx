import {
    useCallback, 
    useEffect,
} from 'react';
import type { MouseEvent } from 'react';
import Modal from 'react-modal';

import { fireEvent } from './Component';
import type { IProps } from './Dialog._types';

import classNames from '@root/utilities/ClassNames';
import './Dialog.scss';

function Dialog<V>(props: IProps<V>) {
    const {
        actionBar = true,
        cancelButtonText = 'Close',
        children,
        confirmButtonText = 'OK',
        dismissable = true,
        open,
        onConfirm,
        onDismiss,
        size,
        title,
        valid = true,
        value,
    } = props;

    const _onDismissDialog = useCallback((ev?: MouseEvent<HTMLButtonElement>) => {
        ev?.preventDefault();
        void fireEvent('Dialog', onDismiss);
    }, [ onDismiss ]);

    const _onConfirm = useCallback((ev: MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        if (valid) {
            void fireEvent('Dialog', onConfirm, value);
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
        onRequestClose={dismissable ? _onDismissDialog : undefined}
        shouldCloseOnEsc={dismissable}
        shouldCloseOnOverlayClick={dismissable}
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
