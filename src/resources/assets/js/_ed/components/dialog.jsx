import React from 'react';
import { Modal } from 'react-bootstrap';

export const EDDialog = props => {
    const factory = props.componentFactory;
    
    // no component factory = nothing to render
    if (! factory) {
        return null;
    }

    if (! (factory instanceof EDComponentFactory)) {
        throw 'Component factory is not an instance of EDComponentFactory.';
    }

    const TitleComponent  = factory ? factory.titleComponent  : undefined;
    const BodyComponent   = factory ? factory.bodyComponent   : undefined;
    const FooterComponent = factory ? factory.footerComponent : undefined;

    const modalProps = props.modalProps || {};
    const componentProps = props.componentProps || {};

    return <Modal {...modalProps}>
        {TitleComponent ? <Modal.Header closeButton>
            <Modal.Title><TitleComponent {...componentProps} /></Modal.Title>
        </Modal.Header> : undefined}
        {BodyComponent ? <Modal.Body><BodyComponent {...componentProps} /></Modal.Body> : undefined}
        {FooterComponent ? <Modal.Footer><FooterComponent {...componentProps} /></Modal.Footer> : undefined}
    </Modal>;
};

export class EDComponentFactory {
    /**
     * Callback for when the component is done interacting with the client.
     */
    set onDone(callback) {
        this.onDoneFunc_ = callback;
    }

    /**
     * Callback for when the component raises an exception as a result from interacting with the client.
     */
    set onFailed(callback) {
        this.onFailureFunc_ = callback;
    }

    /**
     * Optional component for the dialogue title bar.
     */
    get titleComponent() {
        return undefined;
    }
    
    /**
     * Optional component for the dialogue body.
     */
    get bodyComponent() {
        return undefined;
    }

    /**
     * Optional component for the dialogue footer.
     */
    get footerComponent() {
        return undefined;
    }

    /**
     * Informs the invoker that the interaction has come to a conclusion.
     * @param {*} payload - an optional data payload representing the interaction.
     */
    done(payload) {
        if (typeof this.onDoneFunc_ === 'function') {
            this.onDoneFunc_.call(this, payload);
        }
    }

    /**
     * Informs the invoker that the interaction with the client has failed.
     * @param {*} payload - an optional data payload representing the interaction.
     */
    failed(payload) {
        if (typeof this.onFailureFunc_ === 'function') {
            this.onFailureFunc_.call(this, payload);
        }
    }
}
