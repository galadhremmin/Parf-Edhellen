import {
    ISentenceFragmentEntity,
} from '@root/connectors/backend/IBookApi';
import { ITextState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';

export const convertTransformationToString = (text: ITextState, fragments: ISentenceFragmentEntity[]) => {
    if (! text || ! Array.isArray(fragments)) {
        return null;
    }

    return text.paragraphs.reduce((paragraphs, paragraph) => {
        return [
            ...paragraphs,
            paragraph.map((f) => f.fragment).join(''),
        ];
    }, []).join('\n');
};

