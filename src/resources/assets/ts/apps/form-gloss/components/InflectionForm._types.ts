import type { IProps as IParentProps } from '../containers/MasterForm._types';
import type { GroupedInflectionsState } from '../reducers/InflectionsReducer._types';

export interface IProps extends Pick<IParentProps, 'onInflectionsChange' | 'onInflectionCreate'> {
    lexicalEntryId: number;
    inflections: GroupedInflectionsState;
    name: string;
}
