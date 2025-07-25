import { ComponentEventHandler } from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { ILexicalEntryEntity, ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { ISectionsState } from '../../reducers/SectionsReducer._types';

export interface IProps {
    abstract?: string;
    className?: string;
    entityMorph: string;
    languages: ILanguageEntity[];
    sections: ISectionsState<ILexicalEntryEntity>;
    single?: boolean;

    onReferenceClick: ComponentEventHandler<IReferenceLinkClickDetails>
}
