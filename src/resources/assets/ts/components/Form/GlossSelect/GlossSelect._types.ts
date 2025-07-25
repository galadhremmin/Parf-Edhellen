import ILexicalEntryResourceApi from '@root/connectors/backend/IGlossResourceApi';

import { IComponentProps } from '../FormComponent._types';

export interface IProps extends IComponentProps<number> {
    apiConnector?: ILexicalEntryResourceApi;
}
