import { GroupedInflectionsState } from '../reducers/InflectionsReducer._types';

export interface IProps {
    confirmButton: string;
    inflections?: GroupedInflectionsState;
    glossId?: number;
}
