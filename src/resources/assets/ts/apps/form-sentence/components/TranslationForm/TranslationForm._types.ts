import { IProps as IParentProps } from '../../containers/SentenceForm._types';

export interface IProps {
    paragraphs: IParentProps['sentenceParagraphs'];
    translations: IParentProps['sentenceTranslations'];
}

export interface IState {
    paragraphSentenceMap: Map<string, string>;
    lastParagraphsRef: IProps['paragraphs'];
}
