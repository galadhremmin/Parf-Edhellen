import {
    ED_SET_TRANSLATION_DATA, 
    ED_REQUEST_TRANSLATION_GROUPS,
    ED_RECEIVE_TRANSLATION_GROUPS
} from '../reducers/admin';
import axios from 'axios';
import EDConfig from 'ed-config';
import { deferredResolve } from 'ed-promise';

export const setTranslationData = data => ({
    type: ED_SET_TRANSLATION_DATA,
    ...data
});

export const requestTranslationGroups = () => (dispatch, getState) => {
    if (Array.isArray(getState().groups)) {
        return;
    }

    dispatch({
        type: ED_REQUEST_TRANSLATION_GROUPS
    });

    deferredResolve(axios.get(EDConfig.api('book/group')), 800).then(resp => {
        dispatch({
            type: ED_RECEIVE_TRANSLATION_GROUPS,
            groups: resp.data
        });
    });
};
