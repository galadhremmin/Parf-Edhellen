import type { ReactNode } from 'react';

export interface IProps {
    children: ReactNode;
    className?: string;
    /** Resolved lexical entry to load in-place via the `loadReference` global event. */
    lexicalEntryId: number | null;
    title?: string;
    /** Fallback href (right-click/open-in-new-tab, and used verbatim when there's no resolved entry to load in-place). */
    url: string | null;
}
