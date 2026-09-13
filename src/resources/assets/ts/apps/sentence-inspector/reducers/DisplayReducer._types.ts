import type { IReduxAction } from '@root/_types/redux';

/**
 * What the reader has chosen to see. Hiding the translation turns the page into a test;
 * hiding the tengwar turns it into a reader. `changedForms` marks the words written
 * differently from the entry they link to, and is off by default because it is a claim
 * about the language rather than about the text.
 */
export interface IDisplayReducerState {
    changedForms: boolean;
    latin: boolean;
    tengwar: boolean;
    translation: boolean;
}

export type DisplayMode = keyof IDisplayReducerState;

export interface IDisplayReducerAction extends IReduxAction {
    mode?: DisplayMode;
}
