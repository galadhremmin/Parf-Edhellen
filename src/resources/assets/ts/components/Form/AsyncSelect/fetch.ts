import {
    useEffect,
    useState,
} from 'react';

import {
    IdValue,
    ValueLoader,
} from './AsyncSelect._types';

const useFetch = <T>(loader: ValueLoader<T>, value: T | IdValue) => {
    const [ values, setValues ] = useState<T[]>([]);

    useEffect(() => {
        loader(value).then((vs) => {
            setValues(vs);
        });
    }, []);

    return values;
};

export default useFetch;
