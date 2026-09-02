export interface IProfileWordList {
    id: number;
    name: string;
    description?: string;
    numberOfEntries: number;
    /** The first few words in the list, in the owner's own order. */
    previewWords: string[];
    url: string;
}

export interface IProps {
    nickname: string;
    wordLists: IProfileWordList[];
}
