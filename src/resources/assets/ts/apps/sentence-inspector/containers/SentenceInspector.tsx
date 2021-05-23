import React, { useCallback } from 'react';
import { connect } from 'react-redux';
import { ThunkDispatch } from 'redux-thunk';

import { SentenceActions } from '../actions';
import TextInspectorView from '../components/TextInspectorView';
import { RootReducer } from '../reducers';
import { IFragmentInSentenceState, IFragmentsReducerState } from '../reducers/FragmentsReducer._types';
import {
    IEventProps,
    IProps,
} from './SentenceInspector._types';

import './SentenceInspector.scss';
import { fireEventAsync } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';

export function SentenceInspector(props: IProps) {
    const {
        selection,
        latinFragments,
        tengwarFragments,
        translations,
        fragments,

        onFragmentSelect,
    } = props;

    const _onFragmentClick = useCallback((args: IComponentEvent<IFragmentsReducerState>) => {
        const fragment = fragments.find((f) => f.id === args.value?.id);
        fireEventAsync('SentenceInspector', onFragmentSelect, fragment || null);
    }, [ onFragmentSelect, fragments ]);

    const _onNextOrPreviousFragmentClick = useCallback((args: IComponentEvent<number>) => {
        const fragment = fragments.find((f) => f.id === args.value);
        fireEventAsync('SentenceInspector', onFragmentSelect, fragment || null);
    }, [ onFragmentSelect, fragments ]);

    const _onFragmentInSentenceClick = useCallback((args: IComponentEvent<IFragmentInSentenceState>) => {
        const fragment = fragments.find((f) => f.id === args.value?.id);
        fireEventAsync('SentenceInspector', onFragmentSelect, fragment || null);
    }, [ onFragmentSelect, fragments ]);

    const texts = [
        latinFragments,
        tengwarFragments,
    ];
    if (translations.paragraphs.length > 0) {
        texts.push(translations);
    }

    return <div className="sentence-inspector">
        <div className="container">
            <p className="sentence-inspector__introduction">
                Click or tap on a word below to learn about the gloss and the grammar
                rules that apply. The information becomes available on the bottom
                of the screen.
            </p>
        </div>
        <TextInspectorView
            fragment={selection}
            texts={texts}
            onSelectFragment={_onFragmentClick}
            onNextOrPreviousFragmentClick={_onNextOrPreviousFragmentClick}
            onFragmentInSentenceClick={_onFragmentInSentenceClick}
        />
    </div>;
}

const mapStateToProps = (props: RootReducer): IProps => ({
    fragments: props.fragments,
    latinFragments: props.latinFragments,
    selection: props.selection,
    tengwarFragments: props.tengwarFragments,
    translations: props.translations,
});

const mapDispatchToProps = (dispatch: ThunkDispatch<any, any, any>): IEventProps => ({
    onFragmentSelect: async (args) => {
        const actions = new SentenceActions();
        const fragment = args.value;
        dispatch(actions.selectFragment(fragment || null));
    },
});

export default connect(mapStateToProps, mapDispatchToProps)(SentenceInspector);
