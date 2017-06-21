import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import { getCard, testCard } from '../actions';
import { transcribe } from '../../_shared/tengwar';

class EDFlashcards extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
          
        };
    }

    componentDidMount() {
        this.requestNewCard();
    }

    componentWillUnmount() {
        
    }

    requestNewCard() {
        this.props.dispatch(getCard(this.props.flashcardId));
    }

    onOptionClick(ev, option) {
        ev.preventDefault();
        this.props.dispatch(testCard(this.props.flashcardId, option));
    }

    onNextCardClick(ev) {
        ev.preventDefault();
        this.requestNewCard();
    }

    render() {
        const tengwarMode = this.props.tengwarMode;
        const tengwarTranscription = tengwarMode && this.props.word
            ? transcribe(this.props.word, tengwarMode)
            : undefined;

        return <article className={classNames('flip-container', { 'flipped': this.props.flip })}>
            <div className="flipper">
                <section className="front">
                    { this.props.loading ?
                        <div className="sk-spinner sk-spinner-pulse"></div>
                        :
                        <div>
                            <header>
                                <h1>
                                    { this.props.word }
                                    { ' '}
                                    <span className="tengwar">{ tengwarTranscription }</span>
                                </h1>
                            </header>
                            <p>What does this mean?</p>
                            <nav>
                                <ul className="list-group">
                                    { this.props.options.map((option, i) => <li key={i} className="list-group-item">
                                        <a href="#" onClick={ev => this.onOptionClick(ev, option)}>{ option }</a>
                                    </li>) }
                                </ul>
                            </nav>
                        </div> }
                </section>
                <section className="back">
                    { this.props.loading ?
                        <div className="sk-spinner sk-spinner-pulse"></div>
                        : this.props.translation ? <div>
                        <header>
                            <h1>
                                { this.props.word }
                                { ' '}
                                <span className="tengwar">{ tengwarTranscription }</span>
                            </h1>
                        </header>
                        <p>
                            <span className="gloss">{ this.props.translation.translation }</span>
                        </p>
                        { this.props.comments ? <p>{this.props.comments}</p> : '' }
                        { this.props.translation.source ? <span className="source">[{this.props.translation.source}]</span> : '' }
                        { this.props.correct ? 
                            <p className="text-success">
                                <span className="glyphicon glyphicon-ok"></span> 
                                {' '}
                                Correct - great!
                            </p>
                            : <p className="text-danger">
                                <span className="glyphicon glyphicon-remove"></span>
                                {' '}
                                Wrong - better luck next time!
                            </p> }
                        <nav>
                            <ul className="pager">
                                <li className="next">
                                    <a href="#" onClick={this.onNextCardClick.bind(this)}>Next card &rarr;</a>
                                </li>
                            </ul>
                        </nav>
                    </div> : '' }
                </section>
            </div>
        </article>;
    }
}

EDFlashcards.defaultProps = {
    loading: true,
    flashcardId: 0,
    tengwarMode: undefined
};

const mapStateToProps = (state) => {
    return {
        loading: state.loading,
        word: state.word,
        options: state.options,
        translation_id: state.translation_id,
        flip: state.flip,
        translation: state.translation,
        correct: state.correct
    };
};

export default connect(mapStateToProps)(EDFlashcards);
