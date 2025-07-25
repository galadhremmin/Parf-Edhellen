import { IProps as IParentProps } from '../containers/MasterForm._types';
import { ILexicalEntryState } from '../reducers/LexicalEntryReducer._types';

export interface IProps extends Pick<IParentProps, 'onLexicalEntryFieldChange'> {
    lexicalEntry: ILexicalEntryState;
    name: string;
}
