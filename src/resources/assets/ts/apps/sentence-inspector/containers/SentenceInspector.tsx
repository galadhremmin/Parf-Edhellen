import React from 'react';
import { connect } from 'react-redux';

import { IComponentEvent } from '@root/components/Component._types';
import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import SharedReference from '@root/utilities/SharedReference';

import { IRootReducer } from '../reducers';
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

export class SentenceInspector extends React.PureComponent<IProps, IState> {
    public state: IState = {
        fragment: null,
        gloss: null,
    };

    private _actions = new SentenceActions();
    private _api = new SharedReference(BookApiConnector);

    public componentDidMount() {
        const { 
            fragmentId
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
        const fragment = this.props.fragments.find((f) => f.id === id);
        const details = await this._api.value.gloss(fragment.glossId);
        const gloss = details.sections[0].glosses[0];

        gloss.inflectedWord = {
            inflections: fragment.inflections,
            speech: fragment.speech,
            word: fragment.fragment,
        };

        this.setState({
            fragment,
            gloss,
        });

        this.props.dispatch(
            this._actions.selectFragment(fragment),
        );
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
