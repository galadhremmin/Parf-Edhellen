import { useCallback, useEffect, useState } from 'react';
import type { ChangeEvent, KeyboardEvent } from 'react';

import Dialog from '@root/components/Dialog';
import TextIcon from '@root/components/TextIcon';
import ValidationError from '@root/connectors/ValidationError';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import type { IWordList } from '@root/connectors/backend/IWordListApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type { IProps } from './SavePhraseWords._types';

/**
 * Keeps the vocabulary of a whole phrase in one gesture.
 *
 * A reader who has just worked through the King's Letter has met eighty-four words; saving
 * them one heart at a time is not a thing anybody does. Signed-out readers are not offered
 * this at all, rather than being offered it and then turned away.
 */
export default function SavePhraseWords(props: IProps) {
    const {
        lexicalEntryIds,
        phraseName,
    } = props;

    const [ isOpen, setIsOpen ] = useState(false);
    const [ wordLists, setWordLists ] = useState<IWordList[]>([]);
    const [ loading, setLoading ] = useState(false);
    const [ saving, setSaving ] = useState(false);
    const [ errors, setErrors ] = useState<ValidationError>(null);
    const [ savedTo, setSavedTo ] = useState<string>(null);
    const [ showNewForm, setShowNewForm ] = useState(false);
    const [ newListName, setNewListName ] = useState('');

    const _fetchLists = useCallback(async () => {
        setLoading(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            const response = await api.getAll();
            setWordLists(response.wordLists || []);
        } catch (e) {
            setErrors(e instanceof ValidationError ? e : null);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (isOpen) {
            void _fetchLists();
        }
    }, [ isOpen, _fetchLists ]);

    const _onSaveTo = useCallback(async (wordList: IWordList) => {
        setSaving(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            const response = await api.addEntries(wordList.id, lexicalEntryIds);
            const added = response?.numberOfEntries ?? 0;
            setSavedTo(added === 0
                ? `Every word was already in ${wordList.name}.`
                : `Added ${added} ${added === 1 ? 'word' : 'words'} to ${wordList.name}.`);
        } catch (e) {
            setErrors(e instanceof ValidationError ? e : null);
        } finally {
            setSaving(false);
        }
    }, [ lexicalEntryIds ]);

    const _onCreateAndSave = useCallback(async () => {
        if (! newListName.trim()) {
            return;
        }
        setSaving(true);
        setErrors(null);
        try {
            const api = resolve(DI.WordListApi);
            const response = await api.create(newListName.trim());
            const newList = response.wordList;
            await api.addEntries(newList.id, lexicalEntryIds);

            setWordLists((previous) => [ ...previous, newList ]);
            setSavedTo(`Added ${lexicalEntryIds.length} words to ${newList.name}.`);
            setNewListName('');
            setShowNewForm(false);
        } catch (e) {
            setErrors(e instanceof ValidationError ? e : null);
        } finally {
            setSaving(false);
        }
    }, [ lexicalEntryIds, newListName ]);

    const _onNameChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        setNewListName(ev.target.value);
    }, []);

    const _onNameKeyDown = useCallback((ev: KeyboardEvent<HTMLInputElement>) => {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            void _onCreateAndSave();
        }
    }, [ _onCreateAndSave ]);

    if (lexicalEntryIds.length === 0) {
        return null;
    }

    return <>
        <Dialog open={isOpen}
            onDismiss={() => setIsOpen(false)}
            actionBar={false}
            title={<>Keep the words of <em>{phraseName}</em></>}>

            <ValidationErrorAlert error={errors} />

            {savedTo && <p className="alert alert-success">{savedTo}</p>}

            {loading && <p className="text-muted">Loading your word lists&hellip;</p>}

            {! loading && wordLists.length === 0 && ! showNewForm && <p className="text-muted">
                You don&rsquo;t have any word lists yet. Create one to get started!
            </p>}

            {! loading && wordLists.length > 0 && <div className="list-group">
                {wordLists.map((list) => <div key={list.id}
                    className="list-group-item d-flex align-items-center">
                    <div className="flex-grow-1">
                        <span className="fw-medium">{list.name}</span>
                        {list.numberOfEntries != null && <span className="text-muted ms-1 small">
                            ({list.numberOfEntries} {list.numberOfEntries === 1 ? 'word' : 'words'})
                        </span>}
                    </div>
                    <button className="btn btn-sm btn-secondary"
                        disabled={saving}
                        onClick={() => void _onSaveTo(list)}>
                        Add {lexicalEntryIds.length} words
                    </button>
                </div>)}
            </div>}

            {! loading && <div className="mt-3">
                {! showNewForm
                    ? <button className="btn btn-secondary btn-sm" onClick={() => setShowNewForm(true)}>
                        <TextIcon icon="plus-sign" />
                        <span className="ms-1">Create new list</span>
                    </button>
                    : <div className="input-group input-group-sm">
                        <input type="text"
                            className="form-control"
                            placeholder="List name"
                            value={newListName}
                            onChange={_onNameChange}
                            onKeyDown={_onNameKeyDown}
                            autoFocus
                        />
                        <button className="btn btn-primary"
                            disabled={saving || ! newListName.trim()}
                            onClick={() => void _onCreateAndSave()}>
                            Create &amp; add
                        </button>
                        <button className="btn btn-secondary" onClick={() => setShowNewForm(false)}>
                            Cancel
                        </button>
                    </div>}
            </div>}
        </Dialog>

        <button type="button"
            className="phrase-toggle"
            onClick={() => { setSavedTo(null); setIsOpen(true); }}>
            <TextIcon icon="heart" />
            <span className="ms-1">Keep these {lexicalEntryIds.length} words</span>
        </button>
    </>;
}
