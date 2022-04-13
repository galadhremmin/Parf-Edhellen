import { useRef } from 'react';

function useFirstRender() {
    const ref = useRef(true);
    const firstRender = ref.current;
    ref.current = false;
    return firstRender;
}

export default useFirstRender;
