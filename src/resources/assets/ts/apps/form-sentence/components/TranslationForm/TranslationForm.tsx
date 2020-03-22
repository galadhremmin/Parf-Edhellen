import React from 'react';

import {
    createTranslationRows,
} from '../../utilities/translations';
import {
    IProps,
    IState,
    ITranslationRow,
} from './TranslationForm._types';
import TranslationGrid from './TranslationGrid';

export default class TranslationForm extends React.Component<IProps> {
    public static getDerivedStateFromProps(props: IProps, state: IState)
    {
        const {
            paragraphs,
            translations,
        } = props;

        if (paragraphs.length > 0 && translations.length > 0 && //
            paragraphs !== state.lastParagraphsRef) {
            let translationRows: ITranslationRow[] = [];
            try {
                translationRows = createTranslationRows(paragraphs, translations);
            } catch (e) {
                // this is temporarily suppressed as older definitions actually do not have this structure
                // configured by default.
                console.warn(e);
            }

            return {
                lastParagraphsRef: paragraphs, // save the reference
                translationRows,
            } as IState;
        }

        return null;
    }

    public state = {
        lastParagraphsRef: null,
        translationRows: [],
    } as IState;

    public render() {
        const {
            onTranslationChange,
        } = this.props;

        const {
            translationRows,
        } = this.state;

        if (translationRows.length < 1) {
            return <p>You need to complete at least once sentence before you can use this function.</p>;
        }

        return <>
            <p>This is optional but it enhances the experience with an English translation.</p>
            <TranslationGrid rows={translationRows} onChange={onTranslationChange} />
        </>;
    }
}
