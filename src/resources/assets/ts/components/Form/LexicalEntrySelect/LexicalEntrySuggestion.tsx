import Quote from '@root/components/Quote';
import type { ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';

import './LexicalEntrySuggestion.scss';

const LexicalEntrySuggestion = (entry: ISuggestionEntity) => {
    return <span title={entry.comments || ''}>
        <span className="GlossSuggestion--id">{entry.id}</span>
        <span className="GlossSuggestion--word">{entry.word}</span>{' '}
        <span className="GlossSuggestion--type">{entry.type}</span>{' '}
        <span className="GlossSuggestion--gloss"><Quote>{entry.gloss}</Quote></span>{' '}
        {entry.source && `[${entry.source}]`}{' '}
        {entry.lexicalEntryGroupName && `in ${entry.lexicalEntryGroupName}`}
    </span>;
};

export default LexicalEntrySuggestion;
