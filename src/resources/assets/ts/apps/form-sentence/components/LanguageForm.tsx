import { fireEvent } from '@root/components/Component';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import { IProps } from './LanguageForm._types';

export default function LanguageForm(props: IProps) {
    const {
        sentence,
        onLanguageChange
    } = props;

    return <>
        <div className="form-group">
            <label htmlFor="ed-sentence-language">Phrase will be written in the following language</label>
            <LanguageSelect
                className="form-control"
                name="ed-sentence-language"
                includeAllLanguages={false}
                value={sentence.languageId}
                onChange={(ev) => fireEvent('LanguageForm', onLanguageChange, {
                    field: 'languageId',
                    value: ev.value,
                })}
                required={true}
            />
        </div>
    </>;
}