import type { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import type { ITextState } from '@root/apps/sentence-inspector/reducers/FragmentsReducer._types';

export const convertTextComponentsToString = (text: ITextState, fragments: ISentenceFragmentEntity[]) => {
    const paragraphs = convertTextComponentsToParagraphs(text, fragments);
    return convertParagraphsToString(paragraphs);
};

export const convertParagraphsToString = (paragraphs: string[]) => {
    return paragraphs ? paragraphs.join('\n') : null;
};

export const convertTextComponentsToParagraphs = (text: ITextState, fragments: ISentenceFragmentEntity[]): string[] => {
    if (! text || ! Array.isArray(fragments)) {
        return null;
    }

    return text.paragraphs.reduce<string[]>((paragraphs, paragraph) => {
        paragraphs.push(paragraph.map((f) => f.fragment).join(''))
        return paragraphs;
    }, []);
};
