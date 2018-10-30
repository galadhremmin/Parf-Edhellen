import React from 'react';

import {
    ILanguageEntity,
    ILanguagesResponse,
} from '../connectors/backend/BookApiConnector._types';
import LanguageConnector from '../connectors/backend/LanguageConnector';
import {
    FormComponent,
    integerConverter,
} from './FormComponent';

interface IProps {
    value: number;
}

interface IState {
    languages: ILanguagesResponse;
}

/**
 * Represents a `<select>` component for languages, categorized by the time period when they were invented.
 */
export default class LanguageSelect extends FormComponent<number, IProps, IProps, IState> {
    public state: IState = {
        languages: null,
    };

    public async componentWillMount() {
        const languageConnector = new LanguageConnector();
        const languages = await languageConnector.all();

        this.setState({
            languages,
        });
    }

    public render() {
        const props = this.pickComponentProps();
        const languages = this.state.languages;

        if (languages === null) {
            return null;
        }

        const periods = Object.keys(this.state.languages);
        return <select {...props} onChange={this.onChange}>
            <option value={0}>All languages</option>
            {periods.map((period) => <LanguagePeriod key={period} period={period} languages={languages[period]} />)}
        </select>;
    }

    protected convertValue(value: string) {
        return integerConverter(value);
    }
}

/**
 * Represents a single language period (such as "late period") within a `<select>` context.
 * One period contains multiple languages.
 * @param props
 */
const LanguagePeriod = (props: {
    period: string;
    languages: ILanguageEntity[];
}) => {
    if (props.languages.length < 1) {
        return null;
    }

    return <optgroup label={props.period}>
        {props.languages.map((language) => <Language key={language.id} {...language} />)}
    </optgroup>;
};

/**
 * Represents a single language entity within a `<select>` context.
 * @param props
 */
const Language = (props: ILanguageEntity) => {
    return <option value={props.id}>{props.name}</option>;
};
