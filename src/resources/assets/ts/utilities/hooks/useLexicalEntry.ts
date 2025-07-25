import { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { useEffect, useState } from 'react';

import { IGlossHookOptions as ILexicalEntryHookOptions, IHookedLexicalEntry } from './useLexicalEntry._types';

const NoLexicalEntry: IHookedLexicalEntry<any> = {
    error: null,
    lexicalEntry: null,
};

function useLexicalEntry<T extends ILexicalEntryEntity = ILexicalEntryEntity>(lexicalEntryId: number, options: ILexicalEntryHookOptions<T> = {}): IHookedLexicalEntry<T> {
    const {
        isEnabled = undefined, // undefined disables the toggle.
        isVersion = false,
    } = options;

    const [ lexicalEntry, setLexicalEntry ] = useState<IHookedLexicalEntry<T>>(NoLexicalEntry);

    useEffect(() => {
        const disabled = isEnabled !== undefined && ! isEnabled;

        if (! lexicalEntryId) {
            setLexicalEntry(NoLexicalEntry);
        } else if (! disabled) {
            const api = resolve(DI.BookApi);
            (isVersion ? api.lexicalEntryFromVersion(lexicalEntryId) : api.lexicalEntry(lexicalEntryId)) //
                .then((details) => {
                    const entity = details.sections[0].entities[0];
                    let nextLexicalEntry;
                    if (typeof options?.adapter === 'function') {
                        nextLexicalEntry = options.adapter(entity);
                    } else {
                        nextLexicalEntry = entity as T;
                    }

                    setLexicalEntry({
                        error: null,
                        lexicalEntry: nextLexicalEntry,
                    });
                }) //
                .catch((ex) => {
                    const error = `[useLexicalEntry]: Failed to resolve gloss ${lexicalEntryId}: ${ex}`;
                    console.warn(error);
                    setLexicalEntry({
                        error,
                        lexicalEntry: null,
                    });
                });
        }
    }, [ lexicalEntryId, isEnabled ]);

    return lexicalEntry;
}

export default useLexicalEntry;
