import React from 'react';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { fetchResults, beginNavigation, advanceSelection } from '../actions';

class EDSearchToolsApp extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            isReversed: false,
            word: '',
            languageId: 0
        };
        this.throttle = 0;
    }

    componentDidMount() {
        const languageNode = document.getElementById('ed-preloaded-languages');
        this.setState({
            languages: [{
                ID: 0,
                Name: 'All languages'
            }, ...(JSON.parse(languageNode.textContent))]
        });
    }

    searchKeyUp(ev) {
        let direction = ev.which === 40
            ? 1
            : (ev.which === 38 ? -1 : undefined);

        if (direction !== undefined) {
            ev.preventDefault();
            this.props.dispatch(advanceSelection(direction));
        }
    }

    wordChange(ev) {
        this.setState({
            word: ev.target.value
        });

        this.search();
    }

    reverseChange(ev) {
        this.setState({
            isReversed: ev.target.checked
        });

        this.search();
    }

    languageChange(ev) {
        this.setState({
            languageId: parseInt(ev.target.value, /* radix: */ 10)
        });

        this.search();
    }

    search() {
        if (/^\s*$/.test(this.state.word)) {
            return; // empty search result
        }

        if (this.throttle) {
            window.clearTimeout(this.throttle);
        }

        this.throttle = window.setTimeout(() => {
            this.props.dispatch(fetchResults(this.state.word, this.state.isReversed, this.state.languageId));
            this.throttle = 0;
        }, 500);
    }

    navigate(ev) {
        // Override default behaviour
        ev.preventDefault();

        // Dispatch a navigation request
        this.props.dispatch(beginNavigation(this.state.word, undefined));
    }

    render() {
        const fieldClasses = classNames('form-control', { 'disabled': this.props.loading });
        const statusClasses = classNames('glyphicon', this.props.loading
            ? 'glyphicon-refresh loading' : 'glyphicon-search');

        return <form onSubmit={this.navigate.bind(this)}>
            <div className="row">
                <div className="col-md-12">
                    <div className="input-group input-group-lg">
                        <span className="input-group-addon">
                            <span className={statusClasses}>&#32;</span>
                        </span>
                        <input type="search" className={fieldClasses}
                               placeholder="What are you looking for?"
                               tabIndex="1"
                               name="word"
                               autoComplete="off"
                               autoCapitalize="off"
                               autoFocus="true"
                               role="presentation"
                               value={this.state.word}
                               onKeyUp={this.searchKeyUp.bind(this)}
                               onChange={this.wordChange.bind(this)} />
                    </div>
                </div>
            </div>
            <div className="row">
                {this.state.languages ? (
                <select className="search-language-select" onChange={this.languageChange.bind(this)}>
                    {this.state.languages.map(l => <option value={l.ID} key={l.ID}>{l.Name}</option>)}
                </select>) : ''}
                <div className="checkbox input-sm search-reverse-box-wrapper">
                    <label>
                        <input type="checkbox" name="isReversed"
                               checked={this.state.isReversed}
                               onChange={this.reverseChange.bind(this)} /> Reverse search
                    </label>
                </div>
            </div>
        </form>;
    }
}

const mapStateToProps = (state) => {
    return {
        loading: state.loading
    };
};

export default connect(mapStateToProps)(EDSearchToolsApp);
