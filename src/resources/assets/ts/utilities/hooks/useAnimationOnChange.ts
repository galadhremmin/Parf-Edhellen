import { useEffect, useState } from 'react';
import useFirstRender from './useFirstRender';

function useAnimationOnChange<T>(data: T, animationLength: number) {
    const firstRender = useFirstRender();
    const [ isChanged, setIsChanged ] = useState<boolean>(false);

    useEffect(() => {
        if (! firstRender) {
            setIsChanged(true);

            window.setTimeout(() => {
                setIsChanged(false);
            }, animationLength);
        }
    }, [data]);

    return isChanged;
}

export default useAnimationOnChange;
