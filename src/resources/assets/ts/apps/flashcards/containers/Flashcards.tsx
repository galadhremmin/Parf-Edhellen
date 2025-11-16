import { Component, Fragment } from 'react';
import type { MouseEvent } from 'react';
import HtmlInject from '@root/components/HtmlInject';
import Spinner from '@root/components/Spinner';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';
import FlashcardApiConnector from '@root/connectors/backend/FlashcardApiConnector';
import Card from '../components/Card';
import { SideOfTheCard } from '../components/Card._types';
import Table from '../components/Table';
import type {
    IProps,
    IState,
} from './Flashcards._types';

import './Flashcards.scss';

// TODO: Convert to React `functional` component
export default class Flashcards extends Component<IProps, IState> {
    public state: IState = {
        correct: true,
        flipped: false,
        lexicalEntry: null,
        loading: true,
        options: [],
        glossId: null,
        word: null,
    };

    private _api = new FlashcardApiConnector();

    public componentDidMount() {
        void this._loadCard();
    }

    public render() {
        const {
            flipped,
        } = this.state;

        return <Table cardState={flipped ? SideOfTheCard.Back : SideOfTheCard.Front}>
            <Card side={SideOfTheCard.Front}>
                {!flipped && this._renderFront()}
            </Card>
            <Card side={SideOfTheCard.Back}>
                {flipped && this._renderBack()}
            </Card>
        </Table>;
    }

    private _renderFront() {
        const {
            loading,
            options,
            word,
        } = this.state;

        const {
            tengwarMode,
        } = this.props;

        if (loading) {
            return <Spinner />;
        }

        return <Fragment>
            {this._renderHeader(word, tengwarMode)}
            <p>What does this mean?</p>
            <nav>
                <ul className="list-group">
                    {options.map((option) => <li key={option} className="list-group-item">
                        <a href="#" onClick={this._onOptionClick} data-option={option}>{option}</a>
                    </li>)}
                </ul>
            </nav>
        </Fragment>;
    }

    private _renderBack() {
        const {
            correct,
            lexicalEntry: gloss,
            word,
        } = this.state;

        const {
            tengwarMode,
        } = this.props;

        return <Fragment>
            {this._renderHeader(word, tengwarMode)}
            <p>
                <span className="gloss">{gloss.allGlosses}</span>
            </p>
            {gloss.comments && <div className="comments">
                <HtmlInject html={gloss.comments} />
            </div>}
            {gloss.source && <span className="source">[{gloss.source}]</span>}
            {correct ? <p className="text-success">
                <TextIcon icon="ok" />
                {' '}
                That's right! Good job!
            </p> : <p className="text-danger">
                <TextIcon icon="warning-sign" />
                {' '}
                Wrong - better luck next time!
            </p>}
            <nav className="text-center">
                <a href="#" className="btn btn-primary" onClick={this._onNextClick}>Next card</a>
            </nav>
        </Fragment>;
    }

    private _renderHeader(word: string, tengwarMode: string) {
        return <header>
            <h1>
                {word}
                {' '}
                {tengwarMode && <Tengwar text={word} transcribe={true} mode={tengwarMode} />}
            </h1>
        </header>;
    }

    private async _loadCard() {
        this._beginLoading();

        const card = await this._api.card({
            id: this.props.flashcardId,
            not: [],
        });

        this.setState({
            flipped: false,
            options: card.options,
            glossId: card.glossId,
            word: card.word,
        });

        this._endLoading();
    }

    private async _testOption(option: string) {
        this._beginLoading();

        const result = await this._api.test({
            flashcardId: this.props.flashcardId,
            translation: option,
            glossId: this.state.glossId,
        });

        this.setState({
            correct: result.correct,
            flipped: true,
            lexicalEntry: result.lexicalEntry,
        });

        this._endLoading();
    }

    private _beginLoading() {
        this.setState({
            loading: true,
        });
    }

    private _endLoading() {
        this.setState({
            loading: false,
        });
    }

    private _onOptionClick = (ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        const optionKey = 'option';
        const option = (ev.target as HTMLAnchorElement).dataset[optionKey];
        void this._testOption(option);
    }

    private _onNextClick = (ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        void this._loadCard();
    }
}
