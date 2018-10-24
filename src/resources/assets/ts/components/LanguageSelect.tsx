import React from 'react';

import LanguageConnector, { ILanguagesResponse } from '../connectors/backend/LanguageConnector';

interface IState {
    languages: ILanguagesResponse;
}

export default class LanguageSelect extends React.PureComponent<{}, IState> {
    public state = {
        languages: {},
    };

    public async componentWillMount() {
        const languageConnector = new LanguageConnector();
        const languages = await languageConnector.all();

        this.setState({
            languages,
        });
    }

    public render() {
        return <span>{JSON.stringify(this.state.languages)}</span>;
    }
}
