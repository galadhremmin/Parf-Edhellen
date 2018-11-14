export interface IProps {
    sentences: ISentence[][];
}

export interface ISentence {
    id?: number;
    fragment: string;
    sentenceNumber: number;
}
