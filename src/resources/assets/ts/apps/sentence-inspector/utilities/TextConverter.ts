import {
    FragmentTransformation,
    ISentenceFragmentEntity,
    ITextTransformation,
    SentenceFragmentType,
} from '@root/connectors/backend/IBookApi';
import { mapArray } from '@root/utilities/func/mapper';
import {
    IFragmentInSentenceState,
    ITextState,
} from '../reducers/FragmentsReducer._types';

const convert = (transformerName: string, textTransformation: ITextTransformation, //
    fragments: ISentenceFragmentEntity[]) => {
    const text: ITextState = {
        paragraphs: [],
        transformerName,
    };

    if (textTransformation === undefined || textTransformation === null) {
        return text;
    }

    const paragraphNumbers = Object.keys(textTransformation);
    let currentSentenceNumber = 0;

    for (const paragraphNumber of paragraphNumbers) {
        // convert the fragments associated with the current paragraph to store state.
        const state = mapArray<FragmentTransformation, IFragmentInSentenceState>({
            fragment: (v) => typeof v === 'string'
                ? v : v[1] !== undefined ? v[1] : fragments[v[0]].fragment,

            id: (v) => typeof v === 'string' || fragments[v[0]].type !== SentenceFragmentType.Word
                ? 0 : fragments[v[0]].id,

            sentenceNumber: (v) => {
                if (Array.isArray(v)) {
                    currentSentenceNumber = fragments[v[0]].sentenceNumber;
                }

                return currentSentenceNumber;
            },
        }, textTransformation[paragraphNumber]);

        text.paragraphs.push(state);
    }

    return text;
};

export default convert;
