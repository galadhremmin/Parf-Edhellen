import {
    useEffect,
    useState,
} from 'react';

import type {
    IdValue,
    ValueLoader,
} from './AsyncSelect._types';

const useFetch = <T>(loader: ValueLoader<T>, value: T | IdValue) => {
    const [ values, setValues ] = useState<T[]>([]);

    useEffect(() => {
        loader(value).then((vs) => {
            setValues(vs);
        }).catch((e) => {
            console.error(`AsyncSelect value promise: ${e}`);
            setValues([]);
        });
    }, []);

    return values;
};

export default useFetch;
