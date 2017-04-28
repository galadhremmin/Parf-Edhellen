export const deferredResolve = (promise, delayInMs) => {
    if (!delayInMs) {
        throw 'You have to specify a delay.';
    }

    const start = new Date().getTime();
    return promise.then(result => {
        const remainingDelay = -Math.min(0, (new Date().getTime() - start) - delayInMs);

        if (remainingDelay < 1) {
            return result;
        }

        return new Promise(resolve => {
            window.setTimeout(() => {
                resolve(result);
            }, remainingDelay);
        });
    });
};
