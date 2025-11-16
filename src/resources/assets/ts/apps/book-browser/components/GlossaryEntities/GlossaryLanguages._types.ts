import type { ComponentEventHandler } from '@root/components/Component._types';
import type { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import type { ILexicalEntryEntity, ILanguageEntity } from '@root/connectors/backend/IBookApi';
import type { ISectionsState } from '../../reducers/SectionsReducer._types';

export interface IProps {
    abstract?: string;
    className?: string;
    entityMorph: string;
    languages: ILanguageEntity[];
    sections: ISectionsState<ILexicalEntryEntity>;
    single?: boolean;

    onReferenceClick: ComponentEventHandler<IReferenceLinkClickDetails>
}
