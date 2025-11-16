import type { ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import LexicalEntrySuggestion from './LexicalEntrySuggestion';

function LexicalEntryValue(entry: ISuggestionEntity) {
    if (entry === null) {
        return <span>No gloss</span>;
    }

    return LexicalEntrySuggestion(entry);
}

export default LexicalEntryValue;
