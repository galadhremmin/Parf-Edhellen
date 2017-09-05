class ComponentFactory {
    set onDone(callback) {
        this.onDoneFunc_ = callback;
    }

    get titleComponent() {
        return undefined;
    }
    
    get bodyComponent() {
        return undefined;
    }

    get footerComponent() {
        return undefined;
    }

    done(result) {
        if (typeof this.onDoneFunc_ === 'function') {
            this.onDoneFunc_.call(this, result);
        }
    }
}

export default ComponentFactory;
