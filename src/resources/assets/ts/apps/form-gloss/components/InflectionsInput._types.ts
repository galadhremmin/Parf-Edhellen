import type { ComponentEventHandler } from '@root/components/Component._types';
import type { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import type ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import type { GroupedInflectionsState, IInflectionGroupState } from '../reducers/InflectionsReducer._types';

export interface IProps {
    inflections: GroupedInflectionsState;
    inflectionApi?: IInflectionResourceApi;
    focusNextRow?: boolean;

    onChange: ComponentEventHandler<IChangeEventArgs>;

    speechApi?: ISpeechResourceApi;
}

export interface IChangeEventArgs {
    inflection: IInflectionGroupState;
    rowId: string;
}
