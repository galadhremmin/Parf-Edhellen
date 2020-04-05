import {
    ISentenceFragmentEntity,
    ISentenceResponse,
} from '@root/connectors/backend/IBookApi';

import Actions from './Actions';

export default class SentenceActions {
    public setSentence(sentence: ISentenceResponse) {
        return {
            sentence,
            type: Actions.ReceiveSentence,
        };
    }

    public selectFragment(fragment: ISentenceFragmentEntity) {
        if (fragment === null) {
            return {
                fragmentId: null,
                sentenceNumber: null,
                type: Actions.SelectFragment,
            };
        }

        if (typeof window === 'object') {
            window.location.hash = `#!${fragment.sentenceNumber}/${fragment.id}`;
        }

        return {
            fragmentId: fragment.id,
            sentenceNumber: fragment.sentenceNumber,
            type: Actions.SelectFragment,
        };
    }
}
