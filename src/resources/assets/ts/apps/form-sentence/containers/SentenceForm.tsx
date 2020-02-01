import React from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { SentenceActions } from '../actions';
import MetadataForm from '../components/MetadataForm';
import { RootReducer } from '../reducers';
import { IProps } from './SentenceForm._types';

function SentenceForm(props: IProps) {
    const {
        onSentenceFieldChange,
        sentence,
        sentenceFragments,
        sentenceTranslations,
    } = props;

    return <>
        <MetadataForm sentence={sentence} onChange={onSentenceFieldChange} />
    </>;
}

SentenceForm.defaultProps = {
    sentence: null,
    sentenceFragments: [],
} as Partial<IProps>;

const mapStateToProps = (state: RootReducer) => ({
    sentence: state.sentence,
    sentenceFragments: state.sentenceFragments,
    sentenceTranslations: state.sentenceTranslations,
}) as IProps;

const actions = new SentenceActions();
const mapDispatchToProps: any = (dispatch: ReduxThunkDispatch) => ({
    onSentenceFieldChange: (ev) => dispatch(actions.setField(ev.value.field, ev.value.value)),
}) as Partial<IProps>;

export default connect(mapStateToProps, mapDispatchToProps)(SentenceForm);
