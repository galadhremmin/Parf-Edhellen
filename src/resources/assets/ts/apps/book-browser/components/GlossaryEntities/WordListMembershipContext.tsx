import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import type { ReactNode } from 'react';

import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type { ISectionsState } from '../../reducers/SectionsReducer._types';

interface IWordListMembershipContext {
    /** Returns true if the given entry ID is in at least one word list. */
    isSaved: (entryId: number) => boolean;
    /** Optimistically mark an entry as saved (called after a successful add). */
    markSaved: (entryId: number) => void;
}

const WordListMembershipContext = createContext<IWordListMembershipContext>({
    isSaved: () => false,
    markSaved: () => {},
});

export function useWordListMembership() {
    return useContext(WordListMembershipContext);
}

interface IProviderProps {
    sections: ISectionsState;
    children: ReactNode;
}

/**
 * Fetches word-list membership for every lexical entry currently visible
 * in a single batched request.  Anonymous users skip the check entirely.
 */
export function WordListMembershipProvider({ sections, children }: IProviderProps) {
    const [savedIds, setSavedIds] = useState<Set<number>>(new Set());
    const lastCheckedKey = useRef<string>('');

    // Collect all entry IDs from every language section.
    const entryIds = useMemo(() => {
        const ids: number[] = [];
        if (sections) {
            for (const languageId of Object.keys(sections)) {
                const entries = sections[languageId as unknown as number];
                if (Array.isArray(entries)) {
                    for (const entry of entries) {
                        ids.push(entry.id);
                    }
                }
            }
        }
        return ids;
    }, [sections]);

    useEffect(() => {
        if (entryIds.length === 0) {
            return;
        }

        // Skip if the user is not logged in (account id 0)
        const accountId = document.body.getAttribute('data-account-id');
        if (!accountId || accountId === '0') {
            return;
        }

        // Skip if we already checked the exact same set of IDs.
        const key = entryIds.slice().sort((a, b) => a - b).join(',');
        if (key === lastCheckedKey.current) {
            return;
        }
        lastCheckedKey.current = key;

        const api = resolve(DI.WordListApi);
        api.checkMembership(entryIds).then((response) => {
            setSavedIds(new Set(response.savedLexicalEntryIds || []));
        }).catch(() => {
            // Silently degrade — the hearts just won't be filled.
        });
    }, [entryIds]);

    const isSaved = useCallback((entryId: number) => savedIds.has(entryId), [savedIds]);

    const markSaved = useCallback((entryId: number) => {
        setSavedIds((prev) => {
            if (prev.has(entryId)) {
                return prev;
            }
            const next = new Set(prev);
            next.add(entryId);
            return next;
        });
    }, []);

    const value = useMemo(() => ({ isSaved, markSaved }), [isSaved, markSaved]);

    return <WordListMembershipContext.Provider value={value}>
        {children}
    </WordListMembershipContext.Provider>;
}
