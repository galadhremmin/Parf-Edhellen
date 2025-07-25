export interface ISuggestGlossesForFragmentsRequest {
    languageId: number;
    fragment: string;
}

export interface ISuggestGlossesForFragmentsResponse {
    [fragment: string]: Array<{
        lexicalEntryId: number;
        speechId: number;
        inflectionIds: number[];
    }>;
}

export interface ISentenceResourceApi {
    suggestGlossesForFragment(args: ISuggestGlossesForFragmentsRequest): Promise<ISuggestGlossesForFragmentsResponse>;
}
