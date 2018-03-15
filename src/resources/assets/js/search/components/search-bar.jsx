import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import EDAPI from 'ed-api';
import EDLanguageSelect from 'ed-components/language-select';
import { fetchResults, setSelection, advanceSelection } from '../actions';

class EDSearchBar extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isReversed: false,
            word: '',
            languageId: 0,
            includeOld: true
        };
        this.throttle = 0;
        this.keyhook = this.onGlobalKeyPress.bind(this);
    }

    componentDidMount() {
        window.addEventListener('keydown', this.keyhook);
    }

    componentWillUnmount() {
        window.removeEventListener('keydown', this.keyhook);
    }

    search(word) {
        if (word === undefined) {
            word = this.state.word;
        }

        if (/^\s*$/.test(word)) {
            return; // empty search result
        }

        if (this.throttle) {
            window.clearTimeout(this.throttle);
        }

        this.throttle = window.setTimeout(() => {
            this.props.dispatch(fetchResults(word, this.state.isReversed, this.state.languageId, this.state.includeOld));
            this.throttle = 0;
        }, 500);
    }

    navigate(ev) {
        // Override default behaviour
        ev.preventDefault();

        // Dispatch a navigation request
        if (!this.props.loading) {
            this.props.dispatch(setSelection(0));
        }
    }

    onSearchKeyDown(ev) {
        let direction = ev.which === 40
            ? 1
            : (ev.which === 38 ? -1 : undefined);

        if (direction !== undefined) {
            ev.preventDefault();
            this.props.dispatch(advanceSelection(direction));
        }
    }

    onWordChange(ev) {
        const word = ev.target.value;
        this.setState({
            word
        });

        this.search(word);
    }

    onReverseChange(ev) {
        this.setState({
            isReversed: ev.target.checked
        });

        this.search();
    }

    onLanguageChange(ev) {
        this.setState({
            languageId: parseInt(ev.value, /* radix: */ 10)
        });

        this.search();
    }

    onIncludeOldChange(ev) {
        this.setState( {
            includeOld: ev.target.checked
        });

        this.search();
    }

    /**
     * Listens to the client pressing _s_ within the context of the window. When it happens,
     * the focus of the client's attention is drawn to the input field.
     */
    onGlobalKeyPress(ev) {
        if (ev.ctrlKey || ev.metaKey || ev.shiftKey || ev.which !== 83 /* = s */ ||
            // ensure that the target is the body, as in no child elements. This is important
            // as the keyhook will also intercept key presses within text fields etcetera.
            ev.target !== document.body) { 
            return;
        }

        this.inputField.focus();
        ev.preventDefault();
    }

    render() {
        const fieldClasses = classNames('form-control', { 'disabled': this.props.loading });
        const statusClasses = classNames('glyphicon', this.props.loading
            ? 'glyphicon-refresh loading' : 'glyphicon-search');

        return (<form onSubmit={this.navigate.bind(this)}>
            <div className="row">
                <div className="col-md-12">
                    <div className="input-group input-group-lg">
                        <span className="input-group-addon">
                            <span className={statusClasses}>&#32;</span>
                        </span>
                        <input type="search" className={fieldClasses}
                               ref={input => this.inputField = input}
                               placeholder="What are you looking for?"
                               tabIndex={1}
                               accessKey="s"
                               name="word"
                               autoComplete="off"
                               autoCapitalize="off"
                               autoFocus={true}
                               role="presentation"
                               value={this.state.word}
                               onKeyDown={this.onSearchKeyDown.bind(this)}
                               onChange={this.onWordChange.bind(this)} />
                    </div>
                </div>
            </div>
            <div className="row">
                <div className="search-language-select">
                    <label className="inline input-sm">
                        <input type="checkbox" name="isReversed"
                            checked={this.state.isReversed}
                            onChange={this.onReverseChange.bind(this)} /> Reversed
                    </label>
                    <label className="inline input-sm">
                        <input type="checkbox" name="excludeOld"
                            checked={this.state.includeOld}
                            onChange={this.onIncludeOldChange.bind(this)} /> Old sources
                    </label>
                    <EDLanguageSelect onChange={this.onLanguageChange.bind(this)} />
                </div>
            </div>
        </form>);
    }
}

const mapStateToProps = (state) => {
    return {
        loading: state.loading
    };
};

export default connect(mapStateToProps)(EDSearchBar);
