import React, { useCallback } from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import Panel from '@root/components/Panel';
import TextIcon from '@root/components/TextIcon';
import { SentenceActions } from '../actions';
import FragmentsForm from '../components/FragmentsForm';
import MetadataForm from '../components/MetadataForm';
import TranslationForm from '../components/TranslationForm';
import { RootReducer } from '../reducers';
import { IProps } from './SentenceForm._types';

function SentenceForm(props: IProps) {
    const {
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

    const sentenceId = sentence.id;
    const languageId = sentence.languageId;

    const _onSubmit = useCallback((ev) => {
        ev.preventDefault();
        fireEvent('SentenceForm', onSubmit, {
            ...sentence,
            fragments: sentenceFragments,
            translations: sentenceTranslations,
        });
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
        <Panel title="Translations">
            <TranslationForm onTranslationChange={onTranslationChange}
                translations={sentenceTranslations}
                paragraphs={sentenceParagraphs}
            />
        </Panel>
        <div className="text-right">
            {sentenceId && <>
                <button className="btn btn-default" formAction="button" onClick={_onOpenOriginal}>
                    <TextIcon icon="open" />
                    &#32;
                    View original
                </button>
                &#32;
            </>}
            <button className="btn btn-primary" formAction="submit">
                <TextIcon icon="ok" />
                &#32;
                Save contribution
            </button>
        </div>
    </form>;
}

SentenceForm.defaultProps = {
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
