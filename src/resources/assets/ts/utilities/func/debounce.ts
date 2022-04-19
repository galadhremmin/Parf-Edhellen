/* eslint-disable prefer-rest-params */
/* tslint:disable:ban-types no-angle-bracket-type-assertion */

const debounce = <T extends (args: unknown) => void>(waitTimeInMs: number, handler: T): T => {
    let timeout: number = null;

    return <T> <unknown> function () {
        if (timeout !== null) {
            window.clearTimeout(timeout);
            timeout = null;
        }

        // eslint-disable-next-line @typescript-eslint/no-this-alias
        const context = this;
        const args = arguments;

        timeout = window.setTimeout(() => {
            timeout = null;
            handler.apply(context, args);
        }, waitTimeInMs);
    };
};

export default debounce;
