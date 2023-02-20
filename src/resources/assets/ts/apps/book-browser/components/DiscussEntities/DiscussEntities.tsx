import Quote from '@root/components/Quote';
import { IThreadEntity } from '@root/connectors/backend/IDiscussApi';
import { IEntitiesComponentProps } from '../../containers/Entities._types';
import DiscussTable from './DiscussTable';

function DiscussEntities(props: IEntitiesComponentProps<IThreadEntity>) {
    const {
        sections,
        languages: groups,
        word,
    } = props;
    const isMultipleKeywords = word.trim().split(' ').length > 1;
    return <>
        <h2>Threads with the {isMultipleKeywords ? 'keywords' : 'keyword'} <Quote>{word}</Quote></h2>
        <p>The threads are organized by the discussion group they are part of.</p>
        {groups.map((group) => <div key={group.id}>
            <h3>{group.name}</h3>
            <DiscussTable threads={sections[group.id]} />
        </div>)}
    </>;
}

export default DiscussEntities;
