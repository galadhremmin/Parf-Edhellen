import React, { useCallback, useEffect, useState, useRef } from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import {
    DI,
    resolve,
} from '@root/di';
import { fireEvent } from '@root/components/Component';
import Panel from '@root/components/Panel';
import Quote from '@root/components/Quote';
import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import { isEmptyString } from '@root/utilities/func/string-manipulation';
import { SentenceActions } from '../actions';
import FragmentsForm from '../components/FragmentsForm';
import MetadataForm from '../components/MetadataForm';
import TranslationForm from '../components/TranslationForm';
import { RootReducer } from '../reducers';
import { IProps } from './SentenceForm._types';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

function SentenceForm(props: IProps) {
    const {
        bookApi,
        errors,
        onFragmentChange,
        onMetadataChange,
        onParseTextRequest,
        onTextChange,
        onSubmit,
        onTranslationChange,
        sentence,
        sentenceFragments,
        sentenceParagraphs,
        sentenceText,
        sentenceTextIsDirty,
        sentenceTranslations,
    } = props;

    const [ submitted, setSubmitted ] = useState(false);
    const [ currentSentenceName, setCurrentSentenceName ] = useState(null);
    const errorContainer = useRef<HTMLDivElement>();

    const sentenceId = sentence.id;
    const languageId = sentence.languageId;

    useEffect(() => {
        if (errors && errors.errors.size && submitted) {
            makeVisibleInViewport(errorContainer.current);
            setSubmitted(false);
        }
    }, [
        errorContainer,
        errors,
        submitted,
    ]);

    useEffect(() => {
        if (bookApi) {
            bookApi.sentence({ id: sentenceId }).then((s) => {
                setCurrentSentenceName(s.sentence.name);
            });
        }
    }, [ sentenceId ]);

    const _onSubmit = useCallback((ev) => {
        ev.preventDefault();
        let translations: typeof sentenceTranslations = [];
        // Only include translations if they are valid. These are meant to be optional.
        if (sentenceTranslations.length > 0 && //
            ! sentenceTranslations.some((t) => isEmptyString(t.translation))) {
            translations = sentenceTranslations;
        }

        const payload = {
            ...sentence,
            fragments: sentenceFragments,
            translations,
        };
        // leaving `id` will trick the API backend to look for an existing entity
        // with the matching ID, even when it is zero or null.
        if (! payload.id) {
            delete payload.id;
        }

        setSubmitted(true);

        fireEvent('SentenceForm', onSubmit, payload);
    }, [
        onSubmit,
        sentence,
        sentenceFragments,
        sentenceTranslations,
    ]);

    const _onOpenOriginal = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        window.open(`/phrases/${languageId}-default/${sentenceId}-original`, '_blank');
    }, [
        languageId,
        sentenceId,
    ]);

    return <form method="post" action="." onSubmit={_onSubmit}>
        {(sentenceId && currentSentenceName) && <StaticAlert type="info">
            <TextIcon icon="info-sign" />{' '}
            You are proposing a change to the phrase{' '}
            <Quote>{currentSentenceName}</Quote>{' '}
            ({sentence.id}).
        </StaticAlert>}
        <div ref={errorContainer}>
            <ValidationErrorAlert error={errors} />
        </div>
        <Panel title="Basic information">
            <MetadataForm sentence={sentence} onMetadataChange={onMetadataChange} />
        </Panel>
        <Panel title="Phrase">
            <FragmentsForm fragments={sentenceFragments}
                languageId={sentence.languageId}
                text={sentenceText}
                textIsDirty={sentenceTextIsDirty}
                onFragmentChange={onFragmentChange}
                onParseTextRequest={onParseTextRequest}
                onTextChange={onTextChange} />
        </Panel>
        <Panel title="Translations (optional)">
            <TranslationForm onTranslationChange={onTranslationChange}
                translations={sentenceTranslations}
                paragraphs={sentenceParagraphs}
            />
        </Panel>
        <div className="text-right">
            {!! sentenceId && <button className="btn btn-default" formAction="button" onClick={_onOpenOriginal}>
                <TextIcon icon="open" />
                &#32;
                View original
            </button>}
            <button className="btn btn-primary" formAction="submit">
                <TextIcon icon="ok" />
                &#32;
                Save contribution
            </button>
        </div>
    </form>;
}

SentenceForm.defaultProps = {
    bookApi: resolve(DI.BookApi),
    sentence: null,
    sentenceFragments: [],
} as Partial<IProps>;

const mapStateToProps = (state: RootReducer) => ({
    errors: state.errors,
    sentence: state.sentence,
    sentenceFragments: state.sentenceFragments,
    sentenceParagraphs: state.latinText.paragraphs,
    sentenceText: state.latinText.text,
    sentenceTextIsDirty: state.latinText.dirty,
    sentenceTranslations: state.sentenceTranslations,
}) as IProps;

const actions = new SentenceActions();
const mapDispatchToProps: any = (dispatch: ReduxThunkDispatch) => ({
    onFragmentChange: (ev) => dispatch(actions.setFragmentField(ev.value.fragment, ev.value.field, ev.value.value)),
    onMetadataChange: (ev) => dispatch(actions.setMetadataField(ev.value.field, ev.value.value)),
    onParseTextRequest: (ev) => dispatch(actions.reloadFragments(ev.value)),
    onSubmit: (ev) => dispatch(actions.saveSentence(ev.value)),
    onTextChange: (ev) => dispatch(actions.setLatinText(ev.value)),
    onTranslationChange: (ev) => dispatch(actions.setTranslation(ev.value)),
}) as Partial<IProps>;

export default connect(mapStateToProps, mapDispatchToProps)(SentenceForm);
