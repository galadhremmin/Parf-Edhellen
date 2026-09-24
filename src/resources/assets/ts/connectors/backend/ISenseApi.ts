export interface IConceptSuggestion {
    /** How many entries there are to read under this meaning. */
    entries: number;
    definition: string;
    id: number;
    label: string;
    /** What it is a kind of, nearest first: house, building. */
    lineage: string[];
    /** The other words it goes by: WordNet calls one meaning both bungalow and cottage. */
    synonyms: string[];
}

export interface ISenseSuggestion {
    /** How many entries are glossed with this wording. */
    entries: number;
    sense: string;
    senseId: number;
}

export interface ISenseSuggestionsResponse {
    concepts: IConceptSuggestion[];
    senses: ISenseSuggestion[];
}

export default interface ISenseApi {
    /**
     * Offers what a sense might mean: the meanings the taxonomy knows, and the wordings already in use.
     *
     * @param conceptId limits the wordings to those that already mean it.
     */
    find(query: string, conceptId?: number): Promise<ISenseSuggestionsResponse>;
}
