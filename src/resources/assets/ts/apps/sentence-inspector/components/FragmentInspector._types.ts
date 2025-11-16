import type { ComponentEventHandler } from '@root/components/Component._types';

import type { IFragmentsReducerState } from '../reducers/FragmentsReducer._types';

export interface IProps extends IEventProps {
    fragment?: IFragmentsReducerState;
}

export interface IEventProps {
    onNextOrPreviousFragmentClick?: ComponentEventHandler<number>;
    onSelectFragment?: ComponentEventHandler<IFragmentsReducerState>;
}
