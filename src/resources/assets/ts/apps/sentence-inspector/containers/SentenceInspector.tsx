import React from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';

import { IRootReducer } from '../reducers';
import { IProps } from './SentenceInspector._types';

import { SentenceActions } from '../actions';
import { IProps as IFragmentInspectorProps } from '../components/FragmentInspector._types';
import FragmentInspector from '../components/FragmentInspector';
import TextInspectorView from '../components/TextInspectorView';
import { IFragmentInSentenceState } from '../reducers/FragmentsReducer._types';

import './SentenceInspector.scss';

export class SentenceInspector extends React.PureComponent<IProps> {
    private _actions = new SentenceActions();

    public render() {
        return this._render();
    }

    private _render() {
        const {
            selection,
            latinFragments,
            tengwarFragments,
            translations
        } = this.props;

        const texts = [
            latinFragments,
            tengwarFragments,
        ];
        if (translations.paragraphs.length > 0) {
            texts.push(translations);
        }

        return <div className="sentence-inspector">
            <TextInspectorView {...selection}
                fragmentInspector={this._renderInspector}
                texts={texts}
                onFragmentClick={this._onFragmentClick}
            />
        </div>;
    }

    private _renderInspector = (props: IFragmentInspectorProps) => {
        const fragment = this.props.fragments.find(f => f.id === props.fragmentId);
        return <FragmentInspector {...props}
            fragment={fragment}
            onNextFragmentClick={this._onNextFragmentClick}
            onPreviousFragmentClick={this._onPreviousFragmentClick}
        />;
    }

    private _onFragmentClick = (fragment: IFragmentInSentenceState) => {
        this.props.dispatch(
            this._actions.selectFragment(fragment),
        );
    }

    private _onNextFragmentClick = (ev: IComponentEvent<number>) => {

    }

    private _onPreviousFragmentClick = (ev: IComponentEvent<number>) => {

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
