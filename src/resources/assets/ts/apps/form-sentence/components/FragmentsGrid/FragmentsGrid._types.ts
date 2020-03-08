import {
    ColDef,
    ICellRendererParams,
} from '@ag-grid-community/all-modules';

import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

export interface IProps {
    fragments: ISentenceFragmentEntity[];
}

export interface IState {
    gridParameters: Partial<ColDef> & {
        cellRendererParams: IAugmentedCellRendererParams;
    },
}

export interface IAugmentedCellRendererParams extends Partial<ICellRendererParams> {
    speeches: ISpeechEntity[];
}
