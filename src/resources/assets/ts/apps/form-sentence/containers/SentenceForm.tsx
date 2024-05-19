import React, { useCallback, useEffect, useRef, useState } from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import Dialog from '@root/components/Dialog';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import Panel from '@root/components/Panel';
import Quote from '@root/components/Quote';
import Spinner from '@root/components/Spinner';
import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { deepClone } from '@root/utilities/func/clone';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';
import { SentenceActions } from '../actions';
import FragmentsForm from '../components/FragmentsForm';
import LanguageForm from '../components/LanguageForm';
import MetadataForm from '../components/MetadataForm';
import TranslationForm from '../components/TranslationForm';
import { RootReducer } from '../reducers';
import { IProps } from './SentenceForm._types';

import './SentenceForm.scss';

function SentenceForm(props: IProps) {
    const {
        bookApi = resolve(DI.BookApi),
        errors,
        onFragmentChange,
        onMetadataChange,
        onParseTextRequest,
        onTextChange,
        onSubmit,
        onTranslationChange,
        sentence = null,
        sentenceFragments = [],
        sentenceFragmentsLoading,
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
        if (errors && errors.size && submitted) {
            makeVisibleInViewport(errorContainer.current);
            setSubmitted(false);
        }
    }, [
        errorContainer,
        errors,
        submitted,
    ]);

    useEffect(() => {
        if (bookApi && sentenceId !== 0) {
            bookApi.sentence({ id: sentenceId }).then((s) => {
                setCurrentSentenceName(s.sentence.name);
            });
        }
    }, [ sentenceId ]);

    const _onSubmit = useCallback((ev: React.FormEvent) => {
        ev.preventDefault();
        let translations: typeof sentenceTranslations = [];
        // Only include translations if they are valid. These are meant to be optional.
        if (sentenceTranslations?.length > 0) {
            translations = sentenceTranslations;
        }

        const payload = {
            ...(deepClone(sentence)),
            fragments: deepClone(sentenceFragments),
            translations: deepClone(translations),
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
        <Dialog<void> title="Preparing the phrase" open={sentenceFragmentsLoading} dismissable={false}>
            <div className="SentenceForm--loading-dialog-spinner">
                <Spinner />
            </div>
            <p>
                We're currently transcribing the phrase using the appropriate writing system, and we're
                doing our best to identify and inflect the words you've used in the text.
            </p>
            <p>
                Please be patient. This might take a while.
            </p>
        </Dialog>
        {!! (sentenceId && currentSentenceName) && <StaticAlert type="info">
            <TextIcon icon="info-sign" />{' '}
            You are proposing a change to the phrase{' '}
            <Quote>{currentSentenceName}</Quote>{' '}
            ({sentence.id}).
        </StaticAlert>}
        <div ref={errorContainer}>
            <ValidationErrorAlert error={errors} />
        </div>
        <Panel title="Language">
            <LanguageForm sentence={sentence} onLanguageChange={onMetadataChange} />
        </Panel>
        {languageId && <>
            <Panel title="Information about the phrase">
                <MetadataForm sentence={sentence} onMetadataChange={onMetadataChange} />
            </Panel>
            <Panel title="Phrase and words">
                <FragmentsForm fragments={sentenceFragments}
                    languageId={sentence.languageId}
                    text={sentenceText}
                    textIsDirty={sentenceTextIsDirty}
                    onFragmentChange={onFragmentChange}
                    onParseTextRequest={onParseTextRequest}
                    onTextChange={onTextChange} />
            </Panel>
            <Panel title="Translations">
                <TranslationForm onTranslationChange={onTranslationChange}
                    translations={sentenceTranslations}
                    paragraphs={sentenceParagraphs}
                />
            </Panel>
        </>}
        <div className="text-center">
            {!! sentenceId && <button className="btn btn-secondary me-3" formAction="button" onClick={_onOpenOriginal}>
                <TextIcon icon="search" />
                &#32;
                View original
            </button>}
            <button className="btn btn-primary" formAction="submit">
                Save contribution
            </button>
        </div>
    </form>;
}

const mapStateToProps = (state: RootReducer) => ({
    errors: state.errors,
    sentence: state.sentence,
    sentenceFragments: state.sentenceFragments,
    sentenceFragmentsLoading: state.sentenceFragmentsLoading,
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
