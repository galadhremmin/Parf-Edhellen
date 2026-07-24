import { createContext, useCallback, useContext, useMemo } from 'react';
import type { ReactNode } from 'react';

import type { ILanguageEntity } from '@root/connectors/backend/IBookApi';

interface ILanguageLookupContext {
    getLanguage: (id: number) => ILanguageEntity | undefined;
}

const LanguageLookupContext = createContext<ILanguageLookupContext>({
    getLanguage: () => undefined,
});

export function useLanguageLookup() {
    return useContext(LanguageLookupContext);
}

interface IProviderProps {
    languages: ILanguageEntity[];
    children: ReactNode;
}

/**
 * Resolves a language ID to its full entity from the page-wide `languages[]` dictionary the API
 * response already carries, so individual entities/derivation steps don't need to embed a full
 * language object (name, tengwar, ...) each — those repeat across hundreds of entries/steps on a
 * single page.
 */
export function LanguageLookupProvider({ languages, children }: IProviderProps) {
    const map = useMemo(() => new Map(languages.map((language) => [language.id, language])), [languages]);
    const getLanguage = useCallback((id: number) => map.get(id), [map]);
    const value = useMemo(() => ({ getLanguage }), [getLanguage]);

    return <LanguageLookupContext.Provider value={value}>
        {children}
    </LanguageLookupContext.Provider>;
}
