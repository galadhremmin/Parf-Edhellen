import React from 'react';
import EDAPI from 'ed-api';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import Autosuggest from 'react-autosuggest';

class EDAccountSelect extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            ...(this.createStateForValue(props.value)),
            suggestions: []
        };
    }

    componentWillReceiveProps(props) {
        if (props.value) {
            this.setValue(props.value);
        }
    }

    createStateForValue(account) {
        const isId = typeof account === 'number' && isFinite(account) && account !== 0; 

        if (! account || isId) {
            if (isId) {
                const changed = this.state === undefined || (this.state !== undefined && this.state.value != account);

                if (! changed) {
                    return {
                        value: account
                    };
                }

                EDAPI.get(`account/${account}`)
                    .then(resp => this.setValue(resp.data))
                    .catch(resp => this.setValue(undefined));
            }

            return {
                value: 0,
                selectedNickname: undefined,
                nickname: ''
            };
        }

        return {
            value: account.id,
            nickname: account.nickname || '',
            selectedNickname: account.nickname
        };
    }

    /**
     * Sets the account currently selected.
     * @param {Object|number} account - Account object
     */
    setValue(account) {
        const originalValue = this.state.value;
        const state = this.createStateForValue(account);
        this.setState(state);
        
        if (originalValue !== state.value) {
            this.triggerChange();
        }
    }

    /**
     * Gets the id for the account currently selected.
     */
    getValue() {
        return this.state.value;
    }

    /**
     * Gets current visual value.
     */
    getText() {
        return this.state.selectedNickname;
    }

    /**
     * Gives focus to the component's input element.
     */
    focus() {
        const id = this.props.componentId;
        if (! id) {
            return;
        }

        const element = document.getElementById(id);
        if (element) {
            element.focus();
        }
    }

    onNicknameChange(ev, data) {
        this.setState({
            nickname: data.newValue,
            value: this.state.selectedNickname === data.newValue
                ? this.state.value : undefined
        });
    }

    onSuggestionsFetchRequest(data) {
        const nickname = (data.value || '').toLocaleLowerCase();

        // already fetching suggestions?
        if (this.loading || /^\s*$/.test(nickname) || this.state.suggestionsFor === nickname) {
            return;
        }

        // Throttle search requests, to prevent them from occurring too often.
        if (this.searchDelay) {
            window.clearTimeout(this.searchDelay);
            this.searchDelay = 0;
        }

        this.searchDelay = window.setTimeout(() => {
            this.searchDelay = 0;
            this.loading = true;

            // Retrieve suggestions for the specified word.
            EDAPI.post('account/find', { nickname, max: 10 })
            .then(resp => {
                this.setState({
                    suggestions: resp.data,
                    suggestionsFor: nickname
                });

                this.loading = false;
            });

        }, 800);
    }

    onSuggestionsClearRequest() {
        this.setState({
            suggestions: []
        });
    }

    onSuggestionSelect(ev, data) {
        ev.preventDefault();
        this.setValue(data.suggestion || undefined);
    }

    getSuggestionValue(suggestion) {
        return `${suggestion.nickname}`;
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout(() => {
                this.props.onChange({
                    target: this,
                    value: this.getValue()
                });
            }, 0);
        }
    }

    renderInput(inputProps) {
        const valid = !!this.state.value;
        const props = { 
            ...inputProps, 
            className: `form-control ${inputProps.className}`
        };

        return <div className={classNames('input-group', { 'has-warning': !valid && this.props.required, 'has-success': valid })}>
            <input {...props} />
            <div className="input-group-addon">
                <span className={classNames('glyphicon', { 'glyphicon-exclamation-sign': !valid && this.props.required, 'glyphicon-ok': valid })} />
            </div>
        </div>;
    }

    renderSuggestion(suggestion) {
        return <div>{suggestion.nickname} [{suggestion.id}]</div>;
    }

    render() {
        const inputProps = {
            placeholder: 'Search for an account',
            value: this.state.nickname,
            name: this.props.componentName,
            id: this.props.componentId,
            onChange: this.onNicknameChange.bind(this)
        };

        return <div>
            <div>
                <Autosuggest 
                    id={`${this.props.componentId || this.props.componentName}-account-selection`}
                    alwaysRenderSuggestions={false} 
                    multiSection={false}
                    suggestions={this.state.suggestions}
                    onSuggestionsFetchRequested={this.onSuggestionsFetchRequest.bind(this)}
                    onSuggestionsClearRequested={this.onSuggestionsClearRequest.bind(this)}
                    onSuggestionSelected={this.onSuggestionSelect.bind(this)}
                    getSuggestionValue={this.getSuggestionValue.bind(this)}
                    renderInputComponent={this.renderInput.bind(this)}
                    renderSuggestion={this.renderSuggestion.bind(this)}
                    inputProps={inputProps} />
            </div>
        </div>;
    }
}

EDAccountSelect.defaultProps = {
    componentName: 'account',
    componentId: undefined,
    value: 0,
    required: false
};

export default EDAccountSelect;
