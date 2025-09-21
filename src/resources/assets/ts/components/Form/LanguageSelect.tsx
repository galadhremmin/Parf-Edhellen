import React, { useEffect, useMemo, useState } from 'react';
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
function LanguageSelect(props: IProps) {
    const {
        filter = DefaultLanguageFilter,
        formatter = DefaultLanguageFormatter,
        includeAllLanguages = true,
        languageConnector,
        value = 0,
        ...componentProps
    } = props;

    const [languages, setLanguages] = useState<ILanguagesResponse | null>(null);

    useEffect(() => {
        const loadLanguages = async () => {
            if (languageConnector) {
                const languagesData = await languageConnector.all();
                setLanguages(languagesData);
            }
        };
        void loadLanguages();
    }, [languageConnector]);

    const periods = useMemo(() => 
        languages ? Object.keys(languages) : [], 
        [languages]
    );

    const filteredLanguagesByPeriod = useMemo(() => {
        if (!languages) return {};
        
        return periods.reduce((acc, period) => {
            acc[period] = languages[period].filter(filter);
            return acc;
        }, {} as Record<string, ILanguageEntity[]>);
    }, [languages, periods, filter]);

    const handleChange = (ev: React.ChangeEvent<HTMLSelectElement>) => {
        const newValue = integerConverter(ev.target.value);
        if (props.onChange) {
            const componentEvent = {
                value: newValue,
                target: ev.target,
                currentTarget: ev.currentTarget,
                bubbles: ev.bubbles,
                cancelable: ev.cancelable,
                defaultPrevented: ev.defaultPrevented,
                eventPhase: ev.eventPhase,
                isTrusted: ev.isTrusted,
                nativeEvent: ev.nativeEvent,
                preventDefault: () => ev.preventDefault(),
                isDefaultPrevented: () => ev.isDefaultPrevented(),
                stopPropagation: () => ev.stopPropagation(),
                isPropagationStopped: () => ev.isPropagationStopped(),
                persist: () => ev.persist(),
                timeStamp: ev.timeStamp,
                type: ev.type,
            };
            void props.onChange(componentEvent);
        }
    };

    if (languages === null) {
        return null;
    }

    return <select {...componentProps} value={value} onChange={handleChange}>
        {includeAllLanguages && <option value={0}>All languages</option>}
        {!includeAllLanguages && <option value={0}></option>}
        {periods.map((period) => <LanguagePeriod
            key={period}
            period={period}
            languages={filteredLanguagesByPeriod[period] || []}
            formatter={formatter}
        />)}
    </select>;
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
