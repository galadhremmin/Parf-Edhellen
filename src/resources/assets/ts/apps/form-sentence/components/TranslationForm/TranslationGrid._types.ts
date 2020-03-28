import { ColDef } from '@ag-grid-community/all-modules';

import { ComponentEventHandler } from '@root/components/Component._types';
import { ITranslationRow } from './TranslationForm._types';

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
