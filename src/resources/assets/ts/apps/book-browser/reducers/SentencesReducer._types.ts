export interface IMatchedSentence {
    accountId: number;
    description: string;
    id: number;
    isNeologism: boolean;
    languageId: number;
    name: string;
}

export type SentenceReducerState = IMatchedSentence[];
