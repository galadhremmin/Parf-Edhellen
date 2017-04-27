export const deferredResolve = (promise, delayInMs) => {
    const start = new Date().getTime();
    return promise.then(result => {
        const remainingDelay = -Math.min(0, (new Date().getTime() - start) - 800);

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
