import { ISentenceResponse } from '@root/connectors/backend/BookApiConnector._types';
import Actions from '../reducers/Actions';

export default class SentenceActions {
    public setSentence(sentence: ISentenceResponse) {
        return {
            type: Actions.ReceiveSentence,
            sentence,
        };
    }
}
