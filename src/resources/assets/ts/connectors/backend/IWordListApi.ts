export interface IWordList {
    id: number;
    name: string;
    description?: string;
    isPublic?: boolean;
    lexicalEntriesCount?: number;
    /** 0 or 1 — present when getAll is called with a lexicalEntryId filter. */
    containsEntry?: number;
}

export interface IWordListIndexResponse {
    wordLists: IWordList[];
}

export interface IWordListShowResponse {
    wordList: IWordList;
}

export interface ICheckMembershipResponse {
    savedLexicalEntryIds: number[];
}

export interface IWordListApi {
    /**
     * Get all word lists for the authenticated user.
     * When lexicalEntryId is provided, each list includes a `containsEntry`
     * flag (0 or 1) indicating whether it already holds that entry.
     */
    getAll(lexicalEntryId?: number): Promise<IWordListIndexResponse>;

    /**
     * Create a new word list.
     */
    create(name: string, description?: string): Promise<IWordListShowResponse>;

    /**
     * Add a lexical entry to a word list.
     */
    addEntry(wordListId: number, lexicalEntryId: number): Promise<void>;

    /**
     * Remove a lexical entry from a word list.
     */
    removeEntry(wordListId: number, lexicalEntryId: number): Promise<void>;

    /**
     * Batch-check which of the given lexical entry IDs exist in any of
     * the user's word lists.  Returns the subset of IDs that are saved.
     */
    checkMembership(lexicalEntryIds: number[]): Promise<ICheckMembershipResponse>;
}
