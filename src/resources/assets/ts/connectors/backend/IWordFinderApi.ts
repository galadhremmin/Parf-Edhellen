export interface IGloss {
    id: number;
    gloss: string;
    word: string;
}

export interface IWordFinderGame {
    glossary: IGloss[];
}

export interface IWordFinderApi {
    newGame(languageId: number): Promise<IWordFinderGame>;
}
