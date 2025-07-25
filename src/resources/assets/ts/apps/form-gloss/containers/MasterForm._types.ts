import { ComponentEventHandler } from '@root/components/Component._types';
import { FormSection } from '../index._types';
import { RootReducer } from '../reducers';
import { ILexicalEntryState } from '../reducers/LexicalEntryReducer._types';
import { IInflectionGroupState } from '../reducers/InflectionsReducer._types';

export interface IProps extends Partial<Pick<RootReducer, 'changes' | 'errors' | 'lexicalEntry' | 'inflections'>> {
    confirmButton?: string;
    edit?: boolean;
    formSections?: FormSection[];

    onCopyLexicalEntry?: ComponentEventHandler<void>;
    onLexicalEntryFieldChange?: ComponentEventHandler<ILexicalEntryFieldChangeArgs>;
    onInflectionCreate?: ComponentEventHandler<void>;
    onInflectionsChange?: ComponentEventHandler<IInflectionsChangeArgs>;
    onSubmit?: ComponentEventHandler<ISubmitArgs>;
}

export type LexicalEntryProps = keyof ILexicalEntryState;

export interface ILexicalEntryFieldChangeArgs {
    field: LexicalEntryProps;
    value: any;
}

export interface IInflectionsChangeArgs {
    inflectionGroupUuid: string;
    inflectionGroup: IInflectionGroupState;
}

export interface ISubmitArgs extends Partial<Pick<RootReducer, 'changes' | 'lexicalEntry' | 'inflections'>> {
    edit: boolean;
}