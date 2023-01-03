import { ICellEditorParams, ICellRendererParams } from 'ag-grid-community';
import { IGlossEntity, ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

export interface IFragmentGridMetadata extends Partial<ICellEditorParams> {
    groupedInflections?: Map<string, IInflection[]>;
    inflections?: Map<number, IInflection>;
    resolveGloss?: (glossId: number) => Promise<IGlossEntity>;
    speeches?: Map<number, ISpeechEntity>;
    suggestGloss?: (text: string) => Promise<ISuggestionEntity[]>;
}

export type IAugmentedCellRendererParams = IFragmentGridMetadata & Partial<ICellRendererParams>;
