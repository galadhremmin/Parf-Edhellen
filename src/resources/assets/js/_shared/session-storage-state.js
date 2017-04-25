export const loadState = (prefix) => {
    try {
        const serializedState = sessionStorage.getItem(prefix + '-state');
        if (!serializedState) {
            return undefined;
        }

        return JSON.parse(serializedState);
    }
    catch (err) {
        return undefined;
    }
};

export const saveState = (prefix, state) => {
    try {
        const serializedState = JSON.stringify(state);
        sessionStorage.setItem(prefix + '-state', serializedState);
    }
    catch (err) {
        // avoid saving state
    }
};
