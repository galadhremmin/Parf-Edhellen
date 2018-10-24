import React from 'react';

import { IChangeEvent } from '../../../components/FormComponent';
import LanguageSelect from '../../../components/LanguageSelect';
import { ISearchActionState } from '../reducers/constants';

import SearchQueryInput from './SearchQueryInput';

export default class SearchContainer extends React.PureComponent<{}, ISearchActionState> {
    public state: ISearchActionState = {
        includeOld: false,
        languageId: 0,
        query: '',
        reversed: false,
    };

    public render() {
        return <form onSubmit={this._onSubmit}>
            <div className="row">
                <div className="col-md-12">
                    <SearchQueryInput
                        autoFocus={true}
                        name="query"
                        onChange={this._onQueryChange}
                        tabIndex={1}
                        value={this.state.query}
                    />
                </div>
            </div>
            <div className="row">
                <div className="search-language-select">
                    <label className="inline input-sm">
                        <input checked={this.state.reversed}
                            name="reversed"
                            onChange={this._onReverseChange}
                            type="checkbox"
                        /> Reversed
                    </label>
                    <label className="inline input-sm">
                        <input checked={this.state.includeOld}
                            name="excludeOld"
                            onChange={this._onIncludeOldChange}
                            type="checkbox"
                        /> Old sources
                    </label>
                    <LanguageSelect />
                </div>
            </div>
        </form>;
    }

    private _onQueryChange = (ev: IChangeEvent<string>) => {
        console.log(ev.name, ev.value);
    }

    private _onReverseChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        console.log(ev);
    }

    private _onIncludeOldChange = (ev: React.ChangeEvent<HTMLInputElement>) => {
        console.log(ev);
    }

    private _onLanguageChange = (ev: React.ChangeEvent<HTMLSelectElement>) => {
        console.log(ev);
    }

    private _onSubmit = (ev: React.FormEvent<HTMLFormElement>) => {
        ev.preventDefault();
    }
}
