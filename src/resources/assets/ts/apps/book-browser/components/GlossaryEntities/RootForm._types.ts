export interface IProps {
    /** The root's own spelling, e.g. "GAL" — rendered uppercase regardless of input casing. */
    form: string;
    languageId?: number | null;
    lexicalEntryId?: number | null;
    url?: string | null;
}
