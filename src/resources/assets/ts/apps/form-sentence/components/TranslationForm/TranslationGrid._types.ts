import type { ColDef } from '@ag-grid-community/core';

import type { ComponentEventHandler } from '@root/components/Component._types';
import type { ITranslationRow } from './TranslationForm._types';

export type TranslationGridColumnDefinition = (Partial<ColDef> & {
    field: keyof ITranslationRow;
})[];

export interface IState {
    columnDefinition: TranslationGridColumnDefinition;
}

export interface IProps {
    onChange: ComponentEventHandler<ITranslationRow>;
    rows: ITranslationRow[];
}
