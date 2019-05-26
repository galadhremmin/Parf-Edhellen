import {
    useEffect,
    useState,
} from 'react';

import {
    ValueLoader,
} from './AsyncSelect._types';

const useFetch = <T>(loader: ValueLoader<T>, value: T) => {
    const [ values, setValues ] = useState<T[]>([]);

    useEffect(() => {
        loader(value).then((vs) => {
            setValues(vs);
        });
    }, []);

    return values;
};

export default useFetch;
