import type {
    ISentenceFragmentEntity,
    ISentenceResponse,
} from '@root/connectors/backend/IBookApi';
import BoundedCache from '@root/utilities/BoundedCache';

import type { DisplayMode } from '../reducers/DisplayReducer._types';
import Actions from './Actions';

/**
 * The reading trail, kept per phrase. Namespace `phrase.trail` → keys
 * `ed.phrase.trail.<sentenceId>`. Capped, so the store cannot grow without bound as
 * someone works through the corpus; the oldest phrases are evicted first.
 */
const _trailCache = BoundedCache.withLocalStorage<number[]>('phrase.trail', 100);

export default class SentenceActions {
    public setSentence(sentence: ISentenceResponse) {
        return {
            sentence,
            type: Actions.ReceiveSentence,
        };
    }

    /**
     * Restores the words already opened on this phrase. Dispatched after the sentence, since
     * receiving a sentence clears the trail.
     */
    public restoreTrail(sentenceId: number) {
        return {
            trail: (sentenceId ? _trailCache.get(sentenceId.toString(10)) : null) || [],
            type: Actions.RestoreTrail,
        };
    }

    public selectFragment(fragment: ISentenceFragmentEntity, sentenceId = 0) {
        if (typeof window === 'object') {
            // The hash is a deep link into the phrase, so it is cleared when the selection
            // is, rather than being left pointing at a word nobody is looking at.
            window.location.hash = fragment?.id
                ? `#!${fragment.sentenceNumber || 0}/${fragment.id}`
                : '';
        }

        if (fragment?.id && sentenceId) {
            this._rememberOpened(sentenceId, fragment.id);
        }

        return {
            fragment: fragment || null,
            fragmentId: fragment?.id || 0,
            type: Actions.SelectFragment,
        };
    }

    public toggleDisplay(mode: DisplayMode) {
        return {
            mode,
            type: Actions.ToggleDisplay,
        };
    }

    private _rememberOpened(sentenceId: number, fragmentId: number) {
        const key = sentenceId.toString(10);
        const trail = _trailCache.get(key) || [];
        if (! trail.includes(fragmentId)) {
            _trailCache.set(key, [ ...trail, fragmentId ]);
        }
    }
}
