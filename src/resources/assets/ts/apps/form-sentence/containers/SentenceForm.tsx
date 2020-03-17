import React from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import Panel from '@root/components/Panel';
import { SentenceActions } from '../actions';
import FragmentsForm from '../components/FragmentsForm';
import MetadataForm from '../components/MetadataForm';
import { RootReducer } from '../reducers';
import { IProps } from './SentenceForm._types';

function SentenceForm(props: IProps) {
    const {
        onFragmentChange,
        onMetadataChange,
        onParseTextRequest,
        onTextChange,
        sentence,
        sentenceFragments,
        sentenceText,
        sentenceTextIsDirty,
        sentenceTransformations,
        sentenceTranslations,
    } = props;

    return <>
        <Panel title="Basic information">
            <MetadataForm sentence={sentence} onMetadataChange={onMetadataChange} />
        </Panel>
        <Panel title="Phrase">
            <FragmentsForm fragments={sentenceFragments}
                text={sentenceText}
                textIsDirty={sentenceTextIsDirty}
                onFragmentChange={onFragmentChange}
                onParseTextRequest={onParseTextRequest}
                onTextChange={onTextChange} />
        </Panel>
    </>;
}

SentenceForm.defaultProps = {
    sentence: null,
    sentenceFragments: [],
} as Partial<IProps>;

const mapStateToProps = (state: RootReducer) => ({
    sentence: state.sentence,
    sentenceFragments: state.sentenceFragments,
    sentenceText: state.latinText.text,
    sentenceTextIsDirty: state.latinText.dirty,
    sentenceTransformations: state.textTransformations,
    sentenceTranslations: state.sentenceTranslations,
}) as IProps;

const actions = new SentenceActions();
const mapDispatchToProps: any = (dispatch: ReduxThunkDispatch) => ({
    onFragmentChange: (ev) => dispatch(actions.setFragmentField(ev.value.fragment, ev.value.field, ev.value.value)),
    onMetadataChange: (ev) => dispatch(actions.setMetadataField(ev.value.field, ev.value.value)),
    onParseTextRequest: (ev) => dispatch(actions.reloadFragments(ev.value)),
    onTextChange: (ev) => dispatch(actions.setLatinText(ev.value)),
}) as Partial<IProps>;

export default connect(mapStateToProps, mapDispatchToProps)(SentenceForm);
