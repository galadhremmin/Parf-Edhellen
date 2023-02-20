import { ISentenceEntity } from '@root/connectors/backend/IBookApi';
import { IEntitiesComponentProps } from '../../containers/Entities._types';
import Sentences from './Sentences';

function SentencesEntities(props: IEntitiesComponentProps<ISentenceEntity>) {
    const {
        languages,
        sections,
    } = props;
    return languages.map((language) => <Sentences
        key={language.id}
        language={language}
        sentences={sections[language.id] ?? []}
    />)
}

export default SentencesEntities;
