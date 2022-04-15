import React from 'react';

interface ILoadedModule<T extends React.ComponentType> {
    default: T;
}

const lazyComponentLoader = <T extends React.ComponentType>(factory: () => Promise<ILoadedModule<T>>, //
    maxRetries: number = 1, numberOfRetries: number = 0) => {
    return new Promise<ILoadedModule<T>>((resolve, reject) => {
        if (numberOfRetries > maxRetries) {
            reject('Maximum retries while loading component.');
        }

        factory() //
            .then(resolve)
            .catch((error) => {
                // user has entered offline mode
                if (! navigator.onLine) {
                    reject(error); // implement better behaviour here.
                } else {
                    window.setTimeout(() => {
                        lazyComponentLoader(factory, maxRetries, numberOfRetries + 1) //
                            .then(resolve)
                            .catch(reject);
                    }, 1000);
                }
            });
    }); 
}

export default lazyComponentLoader;
