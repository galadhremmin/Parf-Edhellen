import React, {
    useCallback,
} from 'react';
import Modal from 'react-modal';

import { fireEvent } from './Component';
import { IProps } from './Dialog._types';

function Dialog<V>(props: IProps<V>) {
    const {
        actionBar,
        cancelButtonText,
        children,
        confirmButtonText,
        onConfirm,
        onDismiss,
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

     // This is needed so screen readers don't see main content when modal is opened.
     const appElement = document.querySelector('main');
    return <Modal appElement={appElement}
        className="modal-dialog"
        isOpen={props.open}
        style={DialogStyles}>
        <div className="modal-content">
            <div className="modal-header">
                <button type="button" className="close" aria-label="Close" onClick={_onDismissDialog}>
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 className="modal-title">{title}</h4>
            </div>
            <div className="modal-body">
                {children}
            </div>
            {actionBar && <div className="modal-footer">
                <button type="button" className="btn btn-default" onClick={_onDismissDialog}>
                    {cancelButtonText}
                </button>
                {onConfirm && <button type="button" className="btn btn-primary" onClick={_onConfirm}>
                    {confirmButtonText}
                </button>}
            </div>}
        </div>
    </Modal>;
}

Dialog.defaultProps = {
    actionBar: true,
    cancelButtonText: 'Close',
    confirmButtonText: 'OK',
    valid: true,
} as Partial<IProps<any>>;

const DialogStyles = {
    content: {
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
