import type { ILexicalEntryEntity, ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import type { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import type { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';
import type { ICellEditorParams, ICellRendererParams } from '@ag-grid-community/core';

export interface IFragmentGridMetadata extends Partial<ICellEditorParams> {
    groupedInflections?: Map<string, IInflection[]>;
    inflections?: Map<number, IInflection>;
    resolveGloss?: (lexicalEntryId: number) => Promise<ILexicalEntryEntity>;
    speeches?: Map<number, ISpeechEntity>;
    suggestGloss?: (text: string) => Promise<ISuggestionEntity[]>;
}

export type IAugmentedCellRendererParams = IFragmentGridMetadata & Partial<ICellRendererParams>;
