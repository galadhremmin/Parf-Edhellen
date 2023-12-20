import { ComponentEventHandler } from '@root/components/Component._types';
import { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import { GroupedInflectionsState, IInflectionGroupState } from '../reducers/InflectionsReducer._types';

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
