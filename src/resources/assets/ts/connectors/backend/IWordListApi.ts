export interface IWordList {
    id: number;
    name: string;
    description?: string;
    isPublic?: boolean;
    isMine?: boolean;
    /** Number of entries held by the list, when the server was asked to count them. */
    numberOfEntries?: number;
    /** Present when getAll is called with a lexicalEntryId filter. */
    containsEntry?: boolean;
    url?: string;
    createdAt?: string;
    updatedAt?: string;
}

export interface IWordListEntryLanguage {
    id: number;
    name: string;
    shortName: string;
    tengwarMode: string;
}

export interface IWordListEntry {
    lexicalEntryId: number;
    word: string;
    normalizedWord: string;
    tengwar: string | null;
    /** Every gloss on the entry, already joined by the server. */
    translation: string;
    type: string | null;
    language: IWordListEntryLanguage | null;
    /** Canonical dictionary address for the entry, supplied by the server. */
    url: string;
    order: number | null;
    addedAt: string | null;
}

export interface IWordListDetail extends IWordList {
    account: {
        id: number;
        nickname: string;
        url: string;
    } | null;
    entries: IWordListEntry[];
}

export interface IWordListIndexResponse {
    wordLists: IWordList[];
}

export interface IWordListShowResponse {
    wordList: IWordList;
}

export interface IWordListDetailResponse {
    wordList: IWordListDetail;
}

export interface ICheckMembershipResponse {
    savedLexicalEntryIds: number[];
}

export interface IBulkEntriesResponse {
    numberOfEntries: number;
}

export interface IReorderedEntry {
    lexicalEntryId: number;
    order: number;
}

export interface IWordListApi {
    /**
     * Get all word lists for the authenticated user.
     * When lexicalEntryId is provided, each list includes a `containsEntry`
     * flag indicating whether it already holds that entry.
     */
    getAll(lexicalEntryId?: number): Promise<IWordListIndexResponse>;

    /**
     * Get a single word list together with its entries.
     */
    get(wordListId: number): Promise<IWordListDetailResponse>;

    /**
     * Create a new word list.
     */
    create(name: string, description?: string): Promise<IWordListShowResponse>;

    /**
     * Rename a word list or change its visibility.
     */
    update(wordListId: number, changes: Partial<Pick<IWordList, 'name' | 'description' | 'isPublic'>>): Promise<IWordListShowResponse>;

    /**
     * Permanently delete a word list.
     */
    destroy(wordListId: number): Promise<void>;

    /**
     * Add a lexical entry to a word list.
     */
    addEntry(wordListId: number, lexicalEntryId: number): Promise<void>;

    /**
     * Remove a lexical entry from a word list.
     */
    removeEntry(wordListId: number, lexicalEntryId: number): Promise<void>;

    /**
     * Remove several lexical entries from a word list in one request.
     */
    removeEntries(wordListId: number, lexicalEntryIds: number[]): Promise<IBulkEntriesResponse>;

    /**
     * Move (or copy) several lexical entries to another word list.
     */
    moveEntries(wordListId: number, lexicalEntryIds: number[], targetWordListId: number, copy?: boolean): Promise<IBulkEntriesResponse>;

    /**
     * Persist a manual ordering of the list.
     */
    reorderEntries(wordListId: number, entries: IReorderedEntry[]): Promise<void>;

    /**
     * Batch-check which of the given lexical entry IDs exist in any of
     * the user's word lists.  Returns the subset of IDs that are saved.
     */
    checkMembership(lexicalEntryIds: number[]): Promise<ICheckMembershipResponse>;
}
