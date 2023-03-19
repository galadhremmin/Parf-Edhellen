import { useEffect, useState } from 'react';
import {
    createTranslationRows,
} from '../../utilities/translations';
import NoSentencesAlert from '../NoSentencesAlert';
import {
    IProps,
    ITranslationRow,
} from './TranslationForm._types';
import TranslationGrid from './TranslationGrid';

export default function TranslationForm(props: IProps) {
    const [ translationRows, setTranslationRows ] = useState<ITranslationRow[]>([]);

    const {
        onTranslationChange,
        paragraphs,
        translations,
    } = props;

    useEffect(() => {
        // When the paragraphs are reset, the underlying state has changed but not yet been committed.
        // So break out of the change and retain current state.
        if (! Array.isArray(paragraphs) || paragraphs.length < 1) {
            return;
        }
        try {
            const nextTranslationRows = createTranslationRows(paragraphs, translations);
            setTranslationRows(nextTranslationRows);
        } catch (e) {
            // this is temporarily suppressed as older definitions actually do not have this structure
            // configured by default.
            console.warn(e);
        }
    }, [ paragraphs, translations ]);

    if (translationRows.length < 1) {
        return <NoSentencesAlert />;
    }

    return <>
        <p>This is optional but it enhances the experience with an English translation.</p>
        <TranslationGrid rows={translationRows} onChange={onTranslationChange} />
    </>;
}
