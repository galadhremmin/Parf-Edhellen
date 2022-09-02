import { IGlossInflection } from '@root/connectors/backend/IBookApi';
import { IReduxAction } from '@root/_types';

export interface IInflectionAction extends IReduxAction {
    inflectionGroupUuid: string;
    order: number;
    preloadedInflections: IGlossInflection[];
    inflectionGroup: IInflectionGroupState;
}

export interface IInflectionGroupState extends Partial<IGlossInflection> {
    inflections: IGlossInflection[];
}

export type GroupedInflectionsState = IInflectionGroupState[];
