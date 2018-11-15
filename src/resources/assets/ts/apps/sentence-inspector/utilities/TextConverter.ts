import {
    ISentenceFragmentEntity,
    FragmentTransformation,
    TextTransformation,
} from '@root/connectors/backend/BookApiConnector._types';
import { mapArray } from '@root/utilities/func/mapper';
import {
    ITextState,
    IFragmentInSentenceState,
} from '../reducers/FragmentsReducer._types';

const convert = (transformerName: string, textTransformation: TextTransformation, fragments: ISentenceFragmentEntity[]) => {
    const text: ITextState = {
        paragraphs: [],
        transformerName,
    };

    if (textTransformation === undefined || textTransformation === null) {
        return text;
    }

    for (const paragraphMap of textTransformation) {
        const state = mapArray<FragmentTransformation, IFragmentInSentenceState>({
            fragment: (v) => typeof v === 'string'
                ? v : v[1] !== undefined ? v[1] : fragments[v[0]].fragment,
            id: (v) => typeof v === 'string'
                ? -1 : fragments[v[0]].id,
            sentenceNumber: (v) => typeof v === 'string' ? 0 : fragments[v[0]].sentenceNumber,
        }, paragraphMap);

        text.paragraphs.push(state);
    }

    return text;
};

export default convert;
