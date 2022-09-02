import { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import { GroupedInflectionsState } from '../reducers/InflectionsReducer._types';

export interface IProps {
    inflections: GroupedInflectionsState;
    inflectionApi: IInflectionResourceApi;
    speechApi: ISpeechResourceApi;
}
