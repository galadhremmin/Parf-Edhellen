import {
    ED_SET_GLOSS_DATA, 
    ED_REQUEST_GLOSS_GROUPS,
    ED_RECEIVE_GLOSS_GROUPS,
    ED_COMPONENT_IS_READY
} from '../reducers/admin';
import axios from 'axios';
import EDConfig from 'ed-config';
import { deferredResolve } from 'ed-promise';

export const setGlossData = data => ({
    type: ED_SET_GLOSS_DATA,
    ...data
});

export const requestGlossGroups = () => (dispatch, getState) => {
    if (Array.isArray(getState().groups)) {
        return;
    }

    dispatch({
        type: ED_REQUEST_GLOSS_GROUPS
    });

    deferredResolve(axios.get(EDConfig.api('book/group')), 800).then(resp => {
        dispatch({
            type: ED_RECEIVE_GLOSS_GROUPS,
            groups: resp.data
        });
    });
};

export const componentIsReady = () => ({
    type: ED_COMPONENT_IS_READY
});
