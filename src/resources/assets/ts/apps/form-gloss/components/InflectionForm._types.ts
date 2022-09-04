import { IProps as IParentProps } from '../containers/MasterForm._types';
import { GroupedInflectionsState } from '../reducers/InflectionsReducer._types';

export interface IProps extends Pick<IParentProps, 'onInflectionsChange' | 'onInflectionCreate'> {
    glossId: number;
    inflections: GroupedInflectionsState;
    name: string;
}
