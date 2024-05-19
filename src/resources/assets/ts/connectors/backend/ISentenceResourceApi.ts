export interface ISuggestGlossesForFragmentsRequest {
    languageId: number;
    fragment: string;
}

export interface ISuggestGlossesForFragmentsResponse {

}

export interface ISentenceResourceApi {
    suggestGlossesForFragment(args: ISuggestGlossesForFragmentsRequest): Promise<ISuggestGlossesForFragmentsResponse>;
}
