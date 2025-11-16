import type { IProps as IParentProps } from '../containers/MasterForm._types';
import type { ILexicalEntryState } from '../reducers/LexicalEntryReducer._types';

export interface IProps extends Pick<IParentProps, 'onLexicalEntryFieldChange'> {
    lexicalEntry: ILexicalEntryState;
    name: string;
}
