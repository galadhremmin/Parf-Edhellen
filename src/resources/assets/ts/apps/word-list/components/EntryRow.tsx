import { useCallback } from 'react';
import type { ChangeEvent, DragEvent, MouseEvent } from 'react';

import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

import type { IEntryRowProps } from '../containers/WordListDetail._types';
import { abbreviateSpeech } from '../utilities/abbreviations';

/**
 * A single word in the list, rendered as one flowing dictionary line:
 *
 *     Q. aha n. “rage”
 *
 * Deliberately not a grid: the parts vary so much in length that columns leave large gaps, and a
 * table forces horizontal scrolling on a phone rather than wrapping.
 */
function EntryRow(props: IEntryRowProps) {
    const {
        canEdit,
        draggable,
        entry,
        onDragOver,
        onDragStart,
        onDrop,
        onRemove,
        onSelectedChange,
        selected,
    } = props;

    const _onSelectedChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        onSelectedChange(entry.lexicalEntryId, ev.target.checked);
    }, [entry.lexicalEntryId, onSelectedChange]);

    const _onRemove = useCallback(() => {
        onRemove(entry.lexicalEntryId);
    }, [entry.lexicalEntryId, onRemove]);

    const _onDragStart = useCallback(() => {
        onDragStart(entry.lexicalEntryId);
    }, [entry.lexicalEntryId, onDragStart]);

    const _onDragOver = useCallback((ev: DragEvent<HTMLLIElement>) => {
        ev.preventDefault();
        onDragOver(entry.lexicalEntryId);
    }, [entry.lexicalEntryId, onDragOver]);

    /**
     * Loads the entry in the glossary already present on the page instead of navigating away. The
     * book browser is mounted by the default layout, so its `loadReference` listener is always
     * available. `url` remains the anchor's href, so middle click, open-in-new-tab and the context
     * menu keep working; only an unmodified left click is intercepted.
     */
    const _onWordOpen = useCallback((ev: MouseEvent<HTMLAnchorElement>) => {
        if (ev.button !== 0 || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) {
            return;
        }

        ev.preventDefault();

        const globalEvents = resolve(DI.GlobalEvents);
        globalEvents?.fire(globalEvents.loadReference, {
            lexicalEntryId: entry.lexicalEntryId,
        });
    }, [entry.lexicalEntryId]);

    const tengwarMode = entry.language?.tengwarMode;
    const shortName = entry.language?.shortName;

    return <li className={`WordList--item${selected ? ' WordList--item-selected' : ''}`}
               draggable={draggable}
               onDragStart={draggable ? _onDragStart : undefined}
               onDragOver={draggable ? _onDragOver : undefined}
               onDrop={draggable ? onDrop : undefined}>
        {canEdit && <input type="checkbox"
                           className="form-check-input WordList--select"
                           checked={selected}
                           aria-label={`Select ${entry.word}`}
                           onChange={_onSelectedChange} />}

        <span className="WordList--entry">
            {shortName && <span className="WordList--language">
                {shortName.toLocaleUpperCase()}.
            </span>}

            <a className="WordList--word" href={entry.url} onClick={_onWordOpen}>{entry.word}</a>

            {tengwarMode && <span className="WordList--tengwar">
                <Tengwar text={entry.tengwar || entry.word}
                         transcribe={! entry.tengwar}
                         mode={tengwarMode} />
            </span>}

            {entry.type && <em className="WordList--type">{abbreviateSpeech(entry.type)}</em>}

            {entry.translation && <span className="WordList--translation">
                &ldquo;{entry.translation}&rdquo;
            </span>}
        </span>

        {canEdit && <button type="button"
                            className="btn btn-sm btn-link text-danger WordList--remove"
                            title={`Remove ${entry.word} from this list`}
                            onClick={_onRemove}>
            <TextIcon icon="remove" />
        </button>}
    </li>;
}

export default EntryRow;
