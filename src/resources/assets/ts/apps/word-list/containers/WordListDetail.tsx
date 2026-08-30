import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { ChangeEvent } from 'react';

import Spinner from '@root/components/Spinner';
import TextIcon from '@root/components/TextIcon';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type { IWordListDetail, IWordListEntry } from '@root/connectors/backend/IWordListApi';

import EntryRow from '../components/EntryRow';
import type { IProps } from '../index._types';
import { SortOrder } from './WordListDetail._types';

import './WordListDetail.scss';

/** Compares two entries for every sort order except the manual one. */
const comparerFor = (order: SortOrder) => {
    switch (order) {
        case SortOrder.Word:
            return (a: IWordListEntry, b: IWordListEntry) => a.normalizedWord.localeCompare(b.normalizedWord);
        case SortOrder.Translation:
            return (a: IWordListEntry, b: IWordListEntry) => a.translation.localeCompare(b.translation);
        case SortOrder.Language:
            return (a: IWordListEntry, b: IWordListEntry) => (a.language?.name ?? '').localeCompare(b.language?.name ?? '');
        case SortOrder.Type:
            return (a: IWordListEntry, b: IWordListEntry) => (a.type ?? '').localeCompare(b.type ?? '');
        case SortOrder.DateAdded:
            return (a: IWordListEntry, b: IWordListEntry) => (a.addedAt ?? '').localeCompare(b.addedAt ?? '');
        default:
            return null;
    }
};

function WordListDetail(props: IProps) {
    const { canEdit, wordListId } = props;

    const [ wordList, setWordList ] = useState<IWordListDetail>(null);
    const [ entries, setEntries ] = useState<IWordListEntry[]>([]);
    const [ loading, setLoading ] = useState<boolean>(true);
    const [ failed, setFailed ] = useState<boolean>(false);
    const [ busy, setBusy ] = useState<boolean>(false);

    const [ sortOrder, setSortOrder ] = useState<SortOrder>(SortOrder.Manual);
    const [ filterText, setFilterText ] = useState<string>('');
    const [ languageId, setLanguageId ] = useState<number>(0);
    const [ selectedIds, setSelectedIds ] = useState<Set<number>>(new Set());

    const draggedIdRef = useRef<number>(null);
    const dropTargetIdRef = useRef<number>(null);

    useEffect(() => {
        let cancelled = false;

        const api = resolve(DI.WordListApi);
        api.get(wordListId).then((response) => {
            if (cancelled) {
                return;
            }

            setWordList(response.wordList);
            setEntries(response.wordList.entries);
            setLoading(false);
        }).catch(() => {
            if (! cancelled) {
                setFailed(true);
                setLoading(false);
            }
        });

        return () => {
            cancelled = true;
        };
    }, [ wordListId ]);

    /** Languages present in the list, for the filter drop down. */
    const languages = useMemo(() => {
        const byId = new Map<number, string>();
        for (const entry of entries) {
            if (entry.language) {
                byId.set(entry.language.id, entry.language.name);
            }
        }

        return Array.from(byId.entries())
            .map(([ id, name ]) => ({ id, name }))
            .sort((a, b) => a.name.localeCompare(b.name));
    }, [ entries ]);

    const visibleEntries = useMemo(() => {
        const needle = filterText.trim().toLowerCase();

        let filtered = entries;
        if (needle.length > 0) {
            filtered = filtered.filter((entry) => entry.word.toLowerCase().includes(needle)
                || entry.normalizedWord.toLowerCase().includes(needle)
                || entry.translation.toLowerCase().includes(needle));
        }

        if (languageId > 0) {
            filtered = filtered.filter((entry) => entry.language?.id === languageId);
        }

        const comparer = comparerFor(sortOrder);
        if (comparer !== null) {
            // The server already returns the manual order, so sorting is only ever a copy.
            filtered = [ ...filtered ].sort(comparer);
        }

        return filtered;
    }, [ entries, filterText, languageId, sortOrder ]);

    const _onSortOrderChange = useCallback((ev: ChangeEvent<HTMLSelectElement>) => {
        setSortOrder(ev.target.value as SortOrder);
    }, []);

    const _onFilterTextChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        setFilterText(ev.target.value);
    }, []);

    const _onLanguageChange = useCallback((ev: ChangeEvent<HTMLSelectElement>) => {
        setLanguageId(parseInt(ev.target.value, 10));
    }, []);

    const _onSelectedChange = useCallback((lexicalEntryId: number, selected: boolean) => {
        setSelectedIds((previous) => {
            const next = new Set(previous);
            if (selected) {
                next.add(lexicalEntryId);
            } else {
                next.delete(lexicalEntryId);
            }

            return next;
        });
    }, []);

    const _onDeselectAll = useCallback(() => {
        setSelectedIds(new Set());
    }, []);

    const _onSelectAllChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        setSelectedIds(ev.target.checked
            ? new Set(visibleEntries.map((entry) => entry.lexicalEntryId))
            : new Set());
    }, [ visibleEntries ]);

    const _onRemove = useCallback(async (lexicalEntryId: number) => {
        setBusy(true);
        try {
            const api = resolve(DI.WordListApi);
            await api.removeEntry(wordListId, lexicalEntryId);
            setEntries((previous) => previous.filter((entry) => entry.lexicalEntryId !== lexicalEntryId));
            setSelectedIds((previous) => {
                if (! previous.has(lexicalEntryId)) {
                    return previous;
                }

                const next = new Set(previous);
                next.delete(lexicalEntryId);
                return next;
            });
        } finally {
            setBusy(false);
        }
    }, [ wordListId ]);

    const _onRemoveSelected = useCallback(async () => {
        const lexicalEntryIds = Array.from(selectedIds);
        if (lexicalEntryIds.length < 1) {
            return;
        }

        setBusy(true);
        try {
            const api = resolve(DI.WordListApi);
            await api.removeEntries(wordListId, lexicalEntryIds);
            setEntries((previous) => previous.filter((entry) => ! selectedIds.has(entry.lexicalEntryId)));
            setSelectedIds(new Set());
        } finally {
            setBusy(false);
        }
    }, [ selectedIds, wordListId ]);

    const _onDragStart = useCallback((lexicalEntryId: number) => {
        draggedIdRef.current = lexicalEntryId;
    }, []);

    const _onDragOver = useCallback((lexicalEntryId: number) => {
        dropTargetIdRef.current = lexicalEntryId;
    }, []);

    const _onDrop = useCallback(() => {
        const draggedId = draggedIdRef.current;
        const targetId = dropTargetIdRef.current;
        draggedIdRef.current = null;
        dropTargetIdRef.current = null;

        if (draggedId === null || targetId === null || draggedId === targetId) {
            return;
        }

        setEntries((previous) => {
            const reordered = [ ...previous ];
            const from = reordered.findIndex((entry) => entry.lexicalEntryId === draggedId);
            const to = reordered.findIndex((entry) => entry.lexicalEntryId === targetId);

            if (from < 0 || to < 0) {
                return previous;
            }

            const [ moved ] = reordered.splice(from, 1);
            reordered.splice(to, 0, moved);

            const api = resolve(DI.WordListApi);
            void api.reorderEntries(wordListId, reordered.map((entry, order) => ({
                lexicalEntryId: entry.lexicalEntryId,
                order,
            })));

            return reordered;
        });
    }, [ wordListId ]);

    if (loading) {
        return <Spinner />;
    }

    if (failed || wordList === null) {
        return <div className="alert alert-danger">
            We could not load this word list. Please try again later.
        </div>;
    }

    if (entries.length < 1) {
        return <div className="alert alert-info">
            <strong><TextIcon icon="info-sign" /> This list is empty.</strong>
            <p className="mb-0">
                Look up a word and press the heart icon to save it here.
            </p>
        </div>;
    }

    const numberOfSelected = selectedIds.size;
    const allVisibleSelected = visibleEntries.length > 0
        && visibleEntries.every((entry) => selectedIds.has(entry.lexicalEntryId));

    return <div className="WordList">
        <div className="WordList--toolbar">
            <div className="input-group input-group-sm WordList--filter">
                <span className="input-group-text"><TextIcon icon="search" /></span>
                <input type="search"
                       className="form-control"
                       placeholder="Filter by word or translation"
                       aria-label="Filter by word or translation"
                       value={filterText}
                       onChange={_onFilterTextChange} />
            </div>

            {languages.length > 1 && <select className="form-select form-select-sm WordList--language-filter"
                                             aria-label="Filter by language"
                                             value={languageId}
                                             onChange={_onLanguageChange}>
                <option value={0}>All languages</option>
                {languages.map((language) => <option key={language.id} value={language.id}>
                    {language.name}
                </option>)}
            </select>}

            {canEdit && <div className="form-check WordList--select-all">
                <input type="checkbox"
                       id="word-list-select-all"
                       className="form-check-input"
                       checked={allVisibleSelected}
                       onChange={_onSelectAllChange} />
                <label className="form-check-label" htmlFor="word-list-select-all">
                    Select all
                </label>
            </div>}

            <select className="form-select form-select-sm WordList--sort"
                    aria-label="Sort order"
                    value={sortOrder}
                    onChange={_onSortOrderChange}>
                <option value={SortOrder.Manual}>My order</option>
                <option value={SortOrder.Word}>Word</option>
                <option value={SortOrder.Translation}>Translation</option>
                <option value={SortOrder.Type}>Type</option>
                <option value={SortOrder.Language}>Language</option>
                <option value={SortOrder.DateAdded}>Date added</option>
            </select>
        </div>

        {/* Always rendered, so that selecting a word for the first time does not push the list
            down the page. Hidden with `visibility`, which keeps the space reserved. */}
        {canEdit && <div className="WordList--bulk-actions"
                        aria-hidden={numberOfSelected < 1}
                        data-empty={numberOfSelected < 1 ? 'true' : 'false'}>
            <span className="WordList--bulk-count">{numberOfSelected} selected</span>
            <button type="button"
                    className="btn btn-sm btn-outline-danger"
                    disabled={busy}
                    onClick={() => void _onRemoveSelected()}>
                Remove from list
            </button>
            <button type="button"
                    className="btn btn-sm btn-outline-secondary"
                    disabled={busy}
                    onClick={_onDeselectAll}>
                Deselect all
            </button>
        </div>}

        {sortOrder !== SortOrder.Manual && canEdit && <p className="text-muted small">
            Switch back to <em>My order</em> to rearrange the list by hand.
        </p>}

        <ul className="WordList--list">
            {visibleEntries.map((entry) => <EntryRow key={entry.lexicalEntryId}
                                                     entry={entry}
                                                     canEdit={canEdit}
                                                     draggable={canEdit && sortOrder === SortOrder.Manual}
                                                     selected={selectedIds.has(entry.lexicalEntryId)}
                                                     onSelectedChange={_onSelectedChange}
                                                     onRemove={(id) => void _onRemove(id)}
                                                     onDragStart={_onDragStart}
                                                     onDragOver={_onDragOver}
                                                     onDrop={_onDrop} />)}
        </ul>

        {visibleEntries.length < 1 && <p className="text-muted">
            No words match the current filter.
        </p>}
    </div>;
}

export default WordListDetail;
