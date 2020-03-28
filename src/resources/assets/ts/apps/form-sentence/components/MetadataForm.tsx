import React, { useCallback } from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import AccountSelect from '@root/components/Form/AccountSelect';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import { IProps } from './MetadataForm._types';

function MetadataForm(props: IProps) {
    const {
        onMetadataChange,
        sentence,
    } = props;

    const _onChange = useCallback(
        (field: keyof IProps['sentence']) => (ev: IComponentEvent<any>) => {
        fireEvent(null, onMetadataChange, {
            field,
            value: ev.value,
        });
    }, [ onMetadataChange ]);

    const _onChangeNative = useCallback(
        (field: keyof IProps['sentence']) => (ev: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const value = /checkbox|radio/i.test(ev.target.type)
            ? (ev.target as HTMLInputElement).checked : ev.target.value;

        fireEvent(null, onMetadataChange, {
            field,
            value,
        });
    }, [ onMetadataChange ]);

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
                required={true}
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
            <label htmlFor="ed-sentence-description">Abstract</label>
            <textarea className="form-control"
                name="ed-sentence-description"
                value={sentence.description}
                onChange={_onChangeNative('description')}
                required={true}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-sentence-long-description">Details</label>
            <MarkdownInput name="ed-sentence-long-description"
                value={sentence.longDescription}
                onChange={_onChange('longDescription')}
            />
        </div>
    </>;
}

export default MetadataForm;
