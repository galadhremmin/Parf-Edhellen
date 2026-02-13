import { useCallback, useEffect, useState } from 'react';
import type { ChangeEvent, MouseEvent } from 'react';

import Dialog from '@root/components/Dialog';
import TextIcon from '@root/components/TextIcon';
import ValidationError from '@root/connectors/ValidationError';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type { IWordList } from '@root/connectors/backend/IWordListApi';
import type { IProps } from './index._types';
import { useWordListMembership } from '../WordListMembershipContext';

import './SaveToWordList.scss';

function SaveToWordList(props: IProps) {
    const { lexicalEntry } = props;
    const { isSaved, markSaved } = useWordListMembership();

    const [isOpen, setIsOpen] = useState(false);
    const [wordLists, setWordLists] = useState<IWordList[]>([]);
    const [loading, setLoading] = useState(false);
    const [saving, setSaving] = useState(false);
    const [errors, setErrors] = useState<ValidationError>(null);

    // "Create new list" inline form
    const [showNewForm, setShowNewForm] = useState(false);
    const [newListName, setNewListName] = useState('');

    // Track additions and removals made during this dialog session so
    // the UI updates optimistically without re-fetching.
    const [addedToIds, setAddedToIds] = useState<Set<number>>(new Set());
    const [removedFromIds, setRemovedFromIds] = useState<Set<number>>(new Set());

    const _fetchLists = useCallback(async () => {
        setLoading(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            // Pass the entry ID so the API returns containsEntry per list
            const response = await api.getAll(lexicalEntry.id);
            setWordLists(response.wordLists || []);
        } catch (e) {
            setErrors(e instanceof ValidationError ? e : null);
        } finally {
            setLoading(false);
        }
    }, [lexicalEntry.id]);

    const _onOpen = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        setIsOpen(true);
        setAddedToIds(new Set());
        setRemovedFromIds(new Set());
        setShowNewForm(false);
        setNewListName('');
    }, []);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, []);

    // Fetch lists when the dialog opens
    useEffect(() => {
        if (isOpen) {
            void _fetchLists();
        }
    }, [isOpen, _fetchLists]);

    /** Is the entry currently in this list? (server state + local optimistic state) */
    const _isInList = useCallback((list: IWordList) => {
        if (removedFromIds.has(list.id)) {
            return false;
        }
        if (addedToIds.has(list.id)) {
            return true;
        }
        return (list.containsEntry ?? 0) > 0;
    }, [addedToIds, removedFromIds]);

    const _onAddToList = useCallback(async (wordListId: number) => {
        setSaving(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            await api.addEntry(wordListId, lexicalEntry.id);
            markSaved(lexicalEntry.id);
            setAddedToIds((prev) => new Set(prev).add(wordListId));
            setRemovedFromIds((prev) => {
                if (!prev.has(wordListId)) return prev;
                const next = new Set(prev);
                next.delete(wordListId);
                return next;
            });
        } catch (e) {
            if (e instanceof Error && /already exists/i.test(e.message)) {
                markSaved(lexicalEntry.id);
                setAddedToIds((prev) => new Set(prev).add(wordListId));
            } else {
                setErrors(e instanceof ValidationError ? e : null);
            }
        } finally {
            setSaving(false);
        }
    }, [lexicalEntry.id, markSaved]);

    const _onRemoveFromList = useCallback(async (wordListId: number) => {
        setSaving(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            await api.removeEntry(wordListId, lexicalEntry.id);
            setRemovedFromIds((prev) => new Set(prev).add(wordListId));
            setAddedToIds((prev) => {
                if (!prev.has(wordListId)) return prev;
                const next = new Set(prev);
                next.delete(wordListId);
                return next;
            });
        } catch (e) {
            setErrors(e instanceof ValidationError ? e : null);
        } finally {
            setSaving(false);
        }
    }, [lexicalEntry.id]);

    const _onCreateAndAdd = useCallback(async () => {
        if (!newListName.trim()) {
            return;
        }
        setSaving(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            const response = await api.create(newListName.trim());
            const newList = response.wordList;

            await api.addEntry(newList.id, lexicalEntry.id);

            setWordLists((prev) => [...prev, newList]);
            markSaved(lexicalEntry.id);
            setAddedToIds((prev) => new Set(prev).add(newList.id));
            setNewListName('');
            setShowNewForm(false);
        } catch (e) {
            setErrors(e instanceof ValidationError ? e : null);
        } finally {
            setSaving(false);
        }
    }, [newListName, lexicalEntry.id, markSaved]);

    const _onNewListNameChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        setNewListName(ev.target.value);
    }, []);

    const _onNewListKeyDown = useCallback((ev: React.KeyboardEvent<HTMLInputElement>) => {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            void _onCreateAndAdd();
        }
    }, [_onCreateAndAdd]);

    const saved = isSaved(lexicalEntry.id);

    const title = <>
        Save <em>{lexicalEntry.word}</em> to a word list
    </>;

    return <>
        <Dialog open={isOpen}
                onDismiss={_onDismiss}
                actionBar={false}
                title={title}>
            <ValidationErrorAlert error={errors} />

            {loading && <p className="text-muted">Loading your word lists&hellip;</p>}

            {!loading && wordLists.length === 0 && !showNewForm && <p className="text-muted">
                You don&rsquo;t have any word lists yet. Create one to get started!
            </p>}

            {!loading && wordLists.length > 0 && <div className="list-group SaveToWordList--list">
                {wordLists.map((list) => {
                    const inList = _isInList(list);
                    return <div key={list.id}
                                className={`list-group-item d-flex align-items-center${inList ? ' SaveToWordList--item-in-list' : ''}`}>
                        <div className="flex-grow-1 d-flex align-items-center">
                            {inList && <TextIcon icon="heart" className="filled SaveToWordList--heart-indicator" />}
                            <span className="fw-medium">{list.name}</span>
                            {list.lexicalEntriesCount != null &&
                                <span className="text-muted ms-1 small">
                                    ({list.lexicalEntriesCount} {list.lexicalEntriesCount === 1 ? 'word' : 'words'})
                                </span>
                            }
                        </div>
                        {inList
                            ? <button
                                className="btn btn-sm btn-outline-danger"
                                disabled={saving}
                                onClick={() => void _onRemoveFromList(list.id)}
                              >
                                Remove
                              </button>
                            : <button
                                className="btn btn-sm btn-secondary"
                                disabled={saving}
                                onClick={() => void _onAddToList(list.id)}
                              >
                                Add
                              </button>
                        }
                    </div>;
                })}
            </div>}

            {!loading && <div className="mt-3">
                {!showNewForm
                    ? <button
                        className="btn btn-secondary btn-sm"
                        onClick={() => setShowNewForm(true)}
                      >
                        <TextIcon icon="plus-sign" />
                        <span className="ms-1">Create new list</span>
                      </button>
                    : <div className="input-group input-group-sm">
                        <input
                            type="text"
                            className="form-control"
                            placeholder="List name"
                            value={newListName}
                            onChange={_onNewListNameChange}
                            onKeyDown={_onNewListKeyDown}
                            autoFocus
                        />
                        <button
                            className="btn btn-primary"
                            disabled={saving || !newListName.trim()}
                            onClick={() => void _onCreateAndAdd()}
                        >
                            Create &amp; add
                        </button>
                        <button
                            className="btn btn-secondary"
                            onClick={() => setShowNewForm(false)}
                        >
                            Cancel
                        </button>
                      </div>
                }
            </div>}
        </Dialog>
        <a href="#" onClick={_onOpen} title="Save to word list">
            <TextIcon icon="heart" className={saved ? 'filled' : ''} />
        </a>
    </>;
}

export default SaveToWordList;
