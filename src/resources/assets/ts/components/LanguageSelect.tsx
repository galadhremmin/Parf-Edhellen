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
        this.setState({
            languages: await languageConnector.all(),
        });
    }

    public render() {
        return <span>{JSON.stringify(this.state.languages)}</span>;
    }
}
