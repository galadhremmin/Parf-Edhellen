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
    /** Address of the flashcard deck built from this list, supplied by the server. */
    studyUrl?: string;
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

export type FlashcardDirection = 'forward' | 'reverse';

export interface ITengwarText {
    text: string;
    mode: string;
    /** false when `text` is a pre-transcribed override from lexical_entries.tengwar. */
    transcribe: boolean;
}

export interface IFlashcardOption {
    /** Unique within the card even when two options share the same text. Use as the React key. */
    key: string;
    text: string;
    tengwar: ITengwarText | null;
}

export interface IFlashcardCardBack {
    /** Text of the correct option. */
    answer: string;
    /** Which option in `options` is correct - this is how the client scores locally. */
    correctOptionKey: string;
    word: string;
    translations: string[];
    /** Pre-parsed HTML, render with <HtmlInject />. */
    comments: string | null;
    source: string | null;
    url: string;
}

export interface IFlashcardCard {
    /** Stable identity for the card; key <Table> on this so React remounts between cards. */
    cardId: string;
    lexicalEntryId: number;
    glossId: number | null;
    prompt: string;
    promptTengwar: ITengwarText | null;
    options: IFlashcardOption[];
    back: IFlashcardCardBack;
}

export type FlashcardSkippedReason = 'no-translation' | 'no-distractors';

export interface IFlashcardSkipped {
    lexicalEntryId: number;
    word: string;
    reason: FlashcardSkippedReason;
}

export interface IFlashcardDeck {
    wordListId: number;
    wordListName: string;
    direction: FlashcardDirection;
    /** Uniform across every card in the deck. A varying count would leak information. */
    optionCount: number;
    numberOfRequested: number;
    cards: IFlashcardCard[];
    skipped: IFlashcardSkipped[];
}

export interface IFlashcardDeckResponse {
    deck: IFlashcardDeck;
}

export interface IFlashcardAnswer {
    lexicalEntryId: number;
    glossId: number | null;
    /** The option text the user picked. Empty string when they gave up / abandoned. */
    answer: string;
}

export interface IFlashcardResult {
    lexicalEntryId: number;
    correct: boolean;
    expected: string;
    actual: string;
    /** Supplied so the summary need not join back against a deck it has replaced. */
    word: string;
    url: string;
}

export interface IFlashcardResults {
    numberOfCorrect: number;
    numberOfWrong: number;
    cards: IFlashcardResult[];
}

export interface IFlashcardResultsResponse {
    results: IFlashcardResults;
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

    /**
     * Deal a deck of flashcards from the word list. `lexicalEntryIds` narrows the
     * deck to a subset of the list (used by _retry missed words_); the server always
     * intersects it with the list's own membership.
     */
    deck(wordListId: number, direction: FlashcardDirection, limit?: number, lexicalEntryIds?: number[]): Promise<IFlashcardDeckResponse>;

    /**
     * Submit the answers of a finished (or abandoned) deck. The server re-derives
     * correctness from the submitted answer texts; client-side scoring is never trusted.
     */
    deckResults(wordListId: number, direction: FlashcardDirection, answers: IFlashcardAnswer[]): Promise<IFlashcardResultsResponse>;
}
