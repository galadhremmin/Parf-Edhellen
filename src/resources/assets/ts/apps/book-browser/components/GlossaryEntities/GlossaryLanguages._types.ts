import { ComponentEventHandler } from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import { IBookGlossEntity, ILanguageEntity } from '@root/connectors/backend/IBookApi';
import { ISectionsState } from '../../reducers/SectionsReducer._types';

export interface IProps {
    abstract?: string;
    className?: string;
    entityMorph: string;
    languages: ILanguageEntity[];
    sections: ISectionsState<IBookGlossEntity>;
    single?: boolean;

    onReferenceClick: ComponentEventHandler<IReferenceLinkClickDetails>
}
