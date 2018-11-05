/* tslint:disable:ban-types no-angle-bracket-type-assertion */

const debounce = <T extends Function>(waitTimeInMs: number, handler: T): T => {
    let timeout: number = null;

    return <T> <any> function() {
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
