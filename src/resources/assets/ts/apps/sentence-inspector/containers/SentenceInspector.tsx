import { deepEqual } from 'fast-equals';
import React from 'react';
import { connect } from 'react-redux';

import { IRootReducer } from '../reducers';
import {
    IProps,
    IState,
} from './SentenceInspector._types';

import SentenceInspectorView from '../components/SentenceInspectorView';

/*
    insert into sentence_translations (sentence_id, sentence_number, translation)
    values (29, 10, 'Hymn of the elves from Rivendell');
*/
export class SentenceInspector extends React.PureComponent<IProps, IState> {
    /**
     * Builds `leftHand` and `rightHand` state variables based on the corresponding
     * properties passed to the component. The `leftHand` view will consist of tengwar
     * *and* latin transforms when the phrase has a translation, whereas it will only
     * contain the latin transform when there is no translation.
     * @param props new properties
     * @param state existing state
     */
    public static getDerivedStateFromProps(props: IProps, state: IState) {
        const {
            latinFragments,
            tengwarFragments,
            translations,
        } = props;

        const leftHand = translations.length > 0
            ? [tengwarFragments, latinFragments]
            : [latinFragments];
        const rightHand = translations.length > 0
            ? [translations]
            : [tengwarFragments];

        if (deepEqual(leftHand, state.leftHand) && deepEqual(rightHand, state.rightHand)) {
            return null;
        }

        return {
            leftHand,
            rightHand,
        };
    }

    public state: IState = {
        leftHand: null,
        rightHand: null,
    };

    public render() {
        return this._render();
    }

    private _render() {
        const {
            selection,
        } = this.props;
        const {
            leftHand,
            rightHand,
        } = this.state;

        return <div>
            <section>
                <SentenceInspectorView sentences={leftHand} {...selection} />
            </section>
            <section>
                <SentenceInspectorView sentences={rightHand} {...selection} />
            </section>
        </div>;
    }
}

const mapStateToProps = (props: IRootReducer) => ({
    fragments: props.fragments,
    latinFragments: props.latinFragments,
    selection: props.selection,
    tengwarFragments: props.tengwarFragments,
    translations: props.translations,
});

export default connect(mapStateToProps)(SentenceInspector);
