import {
    describe,
    expect,
    test,
} from '@jest/globals';

import Actions from '../actions/Actions';
import DisplayReducer from './DisplayReducer';
import type { IDisplayReducerState } from './DisplayReducer._types';

const toggle = (state: IDisplayReducerState, mode: string) =>
    DisplayReducer(state, { mode, type: Actions.ToggleDisplay } as never);

const defaults: IDisplayReducerState = {
    changedForms: false,
    latin: true,
    tengwar: true,
    translation: true,
};

describe('apps/sentence-inspector/reducers/DisplayReducer', () => {
    test('turns a mode off and on again', () => {
        const off = toggle(defaults, 'translation');
        expect(off.translation).toEqual(false);
        expect(toggle(off, 'translation').translation).toEqual(true);
    });

    test('lets either script be turned off while the other is on', () => {
        expect(toggle(defaults, 'tengwar').tengwar).toEqual(false);
        expect(toggle(defaults, 'latin').latin).toEqual(false);
    });

    test('refuses to turn off the last script standing', () => {
        // With both off the page would have a translation and a word list but no phrase.
        const latinOnly = toggle(defaults, 'tengwar');
        expect(toggle(latinOnly, 'latin')).toEqual(latinOnly);

        const tengwarOnly = toggle(defaults, 'latin');
        expect(toggle(tengwarOnly, 'tengwar')).toEqual(tengwarOnly);
    });

    test('still lets the last script be turned back on after the other returns', () => {
        const latinOnly = toggle(defaults, 'tengwar');
        const both = toggle(latinOnly, 'tengwar');
        expect(both.tengwar).toEqual(true);
        expect(toggle(both, 'latin').latin).toEqual(false);
    });

    test('does not treat the translation as a script', () => {
        const noScriptsButTranslation = toggle(toggle(defaults, 'tengwar'), 'translation');
        expect(noScriptsButTranslation.translation).toEqual(false);
        expect(noScriptsButTranslation.latin).toEqual(true);
    });

    test('resets when a new phrase arrives', () => {
        const changed = toggle(defaults, 'tengwar');
        expect(DisplayReducer(changed, { type: Actions.ReceiveSentence } as never)).toEqual(defaults);
    });
});
