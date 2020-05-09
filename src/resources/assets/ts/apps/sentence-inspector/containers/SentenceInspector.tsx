import React from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import TextIcon from '@root/components/TextIcon';
import IBookApi, { IBookGlossEntity } from '@root/connectors/backend/IBookApi';
import { DI, resolve } from '@root/di';

import { RootReducer } from '../reducers';
import {
    IProps,
    IState,
} from './SentenceInspector._types';

import { SentenceActions } from '../actions';
import FragmentInspector from '../components/FragmentInspector';
import { IProps as IFragmentInspectorProps } from '../components/FragmentInspector._types';
import TextInspectorView from '../components/TextInspectorView';
import { IFragmentInSentenceState } from '../reducers/FragmentsReducer._types';

import './SentenceInspector.scss';

export class SentenceInspector extends React.Component<IProps, IState> {
    public state: IState = {
        fragment: null,
        gloss: null,
    };

    private _actions = new SentenceActions();
    private _api = resolve<IBookApi>(DI.BookApi);

    public componentDidMount() {
        const {
            fragmentId,
        } = this.props.selection;

        if (fragmentId) {
            this._selectFragment(fragmentId);
        }
    }

    public render() {
        const {
            selection,
            latinFragments,
            tengwarFragments,
            translations,
        } = this.props;

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
            <TextInspectorView {...selection}
                fragmentInspector={this._renderInspector}
                texts={texts}
                onFragmentClick={this._onFragmentClick}
            />
        </div>;
    }

    private _renderInspector = (props: IFragmentInspectorProps) => {
        const {
            fragment,
            gloss,
        } = this.state;

        return <FragmentInspector {...props}
            fragment={fragment}
            gloss={gloss}
            onFragmentMoveClick={this._onFragmentMoveClick}
        />;
    }

    private _onFragmentClick = (args: IFragmentInSentenceState) => {
        this._selectFragment(args.id);
    }

    private _onFragmentMoveClick = (ev: IComponentEvent<number>) => {
        this._selectFragment(ev.value);
    }

    private async _selectFragment(id: number) {
        if (id === null) {
            // deselect current fragment
            this.props.dispatch(
                this._actions.selectFragment(null),
            );

            return;
        }

        const fragment = this.props.fragments.find((f) => f.id === id);
        let gloss: IBookGlossEntity;

        try {
            const details = await this._api.gloss(fragment.glossId);
            gloss = details.sections[0].glosses[0];
            gloss.inflectedWord = {
                inflections: fragment.inflections,
                speech: fragment.speech,
                word: fragment.fragment,
            };
        } catch (e) {
            // often 404 -- handle fallback gracefully
            gloss = null;
        }

        this.setState({
            fragment,
            gloss,
        });

        this.props.dispatch(
            this._actions.selectFragment(fragment),
        );
    }
}

const mapStateToProps = (props: RootReducer) => ({
    fragments: props.fragments,
    latinFragments: props.latinFragments,
    selection: props.selection,
    tengwarFragments: props.tengwarFragments,
    translations: props.translations,
});

export default connect(mapStateToProps)(SentenceInspector);
