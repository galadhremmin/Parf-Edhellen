import React from 'react';
import { connect } from 'react-redux';

import StaticAlert from '@root/components/StaticAlert';
import { RootReducer } from '../reducers';
import { IProps } from './SentenceForm._types';

function SentenceForm(props: IProps) {
    const {
        sentence,
        sentenceFragments,
        sentenceTranslations,
    } = props;

    return <StaticAlert type="info">
        Coming soon!
    </StaticAlert>;
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
const mapDispatchToProps: any = undefined;

export default connect(mapStateToProps, mapDispatchToProps)(SentenceForm);
