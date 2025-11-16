import type {
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
        if (fragment?.id && fragment?.sentenceNumber) {
            if (typeof window === 'object') {
                window.location.hash = `#!${fragment.sentenceNumber}/${fragment.id}`;
            }
        }

        return {
            fragment: fragment || null,
            type: Actions.SelectFragment,
        };
    }
}
