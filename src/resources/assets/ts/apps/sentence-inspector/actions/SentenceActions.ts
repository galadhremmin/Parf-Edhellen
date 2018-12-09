import {
    ISentenceFragmentEntity,
    ISentenceResponse,
} from '@root/connectors/backend/BookApiConnector._types';
import Actions from '../reducers/Actions';

export default class SentenceActions {
    public setSentence(sentence: ISentenceResponse) {
        return {
            sentence,
            type: Actions.ReceiveSentence,
        };
    }

    public selectFragment(fragment: ISentenceFragmentEntity) {
        if (typeof window === 'object') {
            window.location.hash = `#!${fragment.id}`;
        }

        return {
            fragmentId: fragment.id,
            sentenceNumber: fragment.sentenceNumber,
            type: Actions.SelectFragment,
        };
    }
}
