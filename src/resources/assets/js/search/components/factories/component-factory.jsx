class ComponentFactory {
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

export default ComponentFactory;
