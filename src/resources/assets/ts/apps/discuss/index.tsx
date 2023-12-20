import { configureStore } from '@reduxjs/toolkit';
import { useEffect } from 'react';
import { Provider } from 'react-redux';
import { thunk } from 'redux-thunk';

import { ReduxThunkDispatch } from '@root/_types';
import { IThreadResponse } from '@root/connectors/backend/IDiscussApi';

import DiscussActions from './actions/DiscussActions';
import Discuss from './containers/Discuss';
import { IProps } from './index._types';
import rootReducer from './reducers';

const store = configureStore({
    reducer: rootReducer,
    middleware: (getDefaultMiddleware) => getDefaultMiddleware().concat(thunk),
 })

const Inject = (props: IProps) => {
    const {
        prefetched,
        thread,
    } = props;

    let {
        entityId,
        entityType,
    } = props;

    useEffect(() => {
        const {
            jumpEnabled,
        } = props;

        const dispatch = store.dispatch as ReduxThunkDispatch;

        const actions = new DiscussActions();
        if (prefetched) {
            if (thread !== undefined) {
                const args = {
                    ...props,
                } as IThreadResponse;
                dispatch(actions.setThread(args, /* updateHistory: */ false, jumpEnabled));
            }
        } else {
            dispatch(actions.thread({
                entityId,
                entityType,
            }, jumpEnabled));
        }
    }, []);

    if (prefetched) {
        entityId = thread.entityId;
        entityType = thread.entityType;
    }

    return <div className="discuss-body">
        <Provider store={store}>
            <Discuss entityId={entityId}
                    entityType={entityType}
                    readonly={props.readonly}
                    highlightThreadPost={props.highlightThreadPost}
            />
        </Provider>
    </div>;
};

Inject.defaultProps = {
    prefetched: true,
    jumpEnabled: true,
    readonly: false,
    highlightThreadPost: false,
} as Partial<IProps>;

export default Inject;
