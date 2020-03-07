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
        onSentenceFieldChange,
        onSentenceTextChange,
        sentence,
        sentenceFragments,
        sentenceText,
        sentenceTransformations,
        sentenceTranslations,
    } = props;

    return <>
        <Panel title="Basic information">
            <MetadataForm sentence={sentence} onChange={onSentenceFieldChange} />
        </Panel>
        <Panel title="Phrase">
            <FragmentsForm text={sentenceText} onChange={onSentenceTextChange} />
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
    sentenceText: state.latinText,
    sentenceTransformations: state.textTransformations,
    sentenceTranslations: state.sentenceTranslations,
}) as IProps;

const actions = new SentenceActions();
const mapDispatchToProps: any = (dispatch: ReduxThunkDispatch) => ({
    onSentenceFieldChange: (ev) => dispatch(actions.setField(ev.value.field, ev.value.value)),
    onSentenceTextChange: (ev) => dispatch(actions.setText(ev.value)),
}) as Partial<IProps>;

export default connect(mapStateToProps, mapDispatchToProps)(SentenceForm);
