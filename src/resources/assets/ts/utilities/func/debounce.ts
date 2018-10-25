const debounce = (waitTimeInMs: number, handler: (...args: any[]) => void) => {
    let timeout: number = null;

    return function() {
        if (timeout !== null) {
            window.clearTimeout(timeout);
            timeout = null;
        }

        const context = this;
        const args = arguments;

        timeout = window.setTimeout(() => {
            timeout = null;
            handler.apply(context, args);
        }, waitTimeInMs);
    };
};

export default debounce;
