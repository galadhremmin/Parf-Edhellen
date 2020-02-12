import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import AccountSelect from '@root/components/Form/AccountSelect';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import { IProps } from './MetadataForm._types';

function MetadataForm(props: IProps) {
    const {
        onChange,
        sentence,
    } = props;

    const _onChange = useCallback(
        (field: keyof IProps['sentence']) => (ev: IComponentEvent<any>) => {
        fireEvent(null, onChange, {
            field,
            value: ev.value,
        });
    }, [ onChange ]);

    const _onChangeNative = useCallback(
        (field: keyof IProps['sentence']) => (ev: React.ChangeEvent<HTMLInputElement>) => {
        const value = /checkbox|radio/i.test(ev.target.type)
            ? ev.target.checked : ev.target.value;

        fireEvent(null, onChange, {
            field,
            value,
        });
    }, [ onChange ]);

    return <>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-name" className="control-label">Name</label>
            <input type="text"
                className="form-control"
                id="ed-sentence-name"
                value={sentence.name}
                onChange={_onChangeNative('name')}
                required={true}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-language">Language</label>
            <LanguageSelect
                className="form-control"
                name="ed-sentence-language"
                value={sentence.languageId}
                onChange={_onChange('languageId')}
                required={true}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-source" className="control-label">Source</label>
            <input type="text"
                className="form-control"
                id="ed-sentence-source"
                value={sentence.source}
                onChange={_onChangeNative('source')}
                required={true}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-account">Account</label>
            <AccountSelect
                name="ed-sentence-account"
                onChange={_onChange('account')}
                value={sentence.account}
            />
        </div>
        <div className="checkbox">
            <label>
                <input type="checkbox"
                    name="ed-sentence-is-neologism"
                    checked={sentence.isNeologism}
                    value={1}
                    onChange={_onChangeNative('isNeologism')}
                /> Neologism
            </label>
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-long-description">Description</label>
            <MarkdownInput name="ed-sentence-long-description"
                value={sentence.longDescription}
                onChange={_onChange('longDescription')}
            />
        </div>
        <pre>
            {JSON.stringify(sentence, null, 2)}
        </pre>
    </>;
}

export default MetadataForm;
