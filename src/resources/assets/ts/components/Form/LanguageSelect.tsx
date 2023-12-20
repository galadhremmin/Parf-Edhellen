import {
  ILanguageEntity,
  ILanguagesResponse,
} from '@root/connectors/backend/IBookApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import {
  FormComponent,
  integerConverter,
} from './FormComponent';
import { IComponentProps } from './FormComponent._types';

interface IProps extends IComponentProps<number> {
    filter?: (language: ILanguageEntity) => boolean;
    formatter?: (language: ILanguageEntity) => string;
    includeAllLanguages?: boolean;
    languageConnector?: ILanguageApi;
}

interface IState {
    languages: ILanguagesResponse;
}

export const DefaultLanguageFilter = () => true;
export const LanguageWithWritingModeOnlyFilter = (language: ILanguageEntity) => //
    typeof language.tengwarMode === 'string' && language.tengwarMode.length > 0;

export const DefaultLanguageFormatter = (language: ILanguageEntity) => language.name;
export const LanguageAndWritingModeFormatter = (language: ILanguageEntity) => //
    `${language.name} (${language.tengwarMode})`;

/**
 * Represents a `<select>` component for languages, categorized by the time period when they were invented.
 */
export class LanguageSelect extends FormComponent<number, IProps, IProps, IState> {
    public static defaultProps = {
        filter: DefaultLanguageFilter,
        formatter: DefaultLanguageFormatter,
        includeAllLanguages: true,
        value: 0,
    } as Partial<IProps>;

    public state: IState = {
        languages: null,
    };

    public async componentDidMount() {
        const {
            languageConnector,
        } = this.props;
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
        const filter = this.props.filter || DefaultLanguageFilter;
        const formatter = this.props.formatter || DefaultLanguageFormatter;
        const includeAllLanguages = this.props.includeAllLanguages;

        return <select {...props} onChange={this.onBackingComponentChange}>
            {includeAllLanguages && <option value={0}>All languages</option>}
            {!includeAllLanguages && <option value={0}></option>}
            {periods.map((period) => <LanguagePeriod
                key={period}
                period={period}
                languages={languages[period].filter(filter)}
                formatter={formatter}
            />)}
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
    formatter: IProps['formatter'],
}) => {
    if (props.languages.length < 1) {
        return null;
    }

    return <optgroup label={props.period}>
        {props.languages.map((language) => <option key={language.id} value={language.id}>
            {props.formatter(language)}
        </option>)}
    </optgroup>;
};

export default withPropInjection(LanguageSelect, {
    languageConnector: DI.LanguageApi,
});
