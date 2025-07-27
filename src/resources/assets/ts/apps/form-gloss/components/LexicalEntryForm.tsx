import { ChangeEvent } from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import AccountSelect from '@root/components/Form/AccountSelect';
import GlossGroupSelect from '@root/components/Form/GlossGroupSelect';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import OptionalLabel from '@root/components/Form/OptionalLabel';
import SpeechSelect from '@root/components/Form/SpeechSelect';
import TagInput from '@root/components/Form/TagInput';
import TengwarInput from '@root/components/Form/TengwarInput';
import Panel from '@root/components/Panel';

import LexicalEntryDetailInput from './LexicalEntryDetailInput';
import { LexicalEntryProps } from '../containers/MasterForm._types';
import {
    defaultTransformer,
    keywordsTransformer,
    senseTransformer,
    glossesTransformer,
    wordTransformer,
} from '../utilities/value-transformers';
import { ValueTransformer } from '../utilities/value-transformers._types';
import { IProps } from './LexicalEntryForm._types';

function LexicalEntryForm(props: IProps) {
    const {
        name = 'GlossForm',
        onLexicalEntryFieldChange,
        lexicalEntry = null,
    } = props;

    const _onFieldChange = (field: LexicalEntryProps, value: string) => {
        const params = {
            field,
            value,
        };

        void fireEvent(name, onLexicalEntryFieldChange, params);
    };

    const _onChangeNative = (field: LexicalEntryProps, transform: ValueTransformer = defaultTransformer) =>
        (e: ChangeEvent<HTMLInputElement>) => {
        const value = transform(e.target.type === 'checkbox' || e.target.type === 'radio'
            ? e.target.checked
            : e.target.value,
        );
        _onFieldChange(field, value);
    };

    const _onChange = (field: LexicalEntryProps, transform: ValueTransformer = defaultTransformer) =>
        (e: IComponentEvent<any>) => {
        const value = transform(e.value);
        _onFieldChange(field, value);
    };

    return <>
        <div className="row">
            <div className="col-sm-12 col-lg-6">
                <Panel title="Basic information">
                    <div className="form-group">
                        <label htmlFor="ed-gloss-word" className="control-label">Word</label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-word"
                            value={lexicalEntry.word.word}
                            onChange={_onChangeNative('word', wordTransformer)}
                            required={true}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-sense-word" className="control-label">Sense</label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-sense-word"
                            value={lexicalEntry.sense.word.word}
                            onChange={_onChangeNative('sense', senseTransformer)}
                            required={true}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-language">Language</label>
                        <LanguageSelect
                            className="form-control"
                            name="ed-gloss-language"
                            value={lexicalEntry.languageId}
                            onChange={_onChange('languageId')}
                            required={true}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-translations">Glosses</label>
                        <TagInput
                            name="ed-gloss-glosses"
                            value={lexicalEntry.glosses.map((t) => t.translation)}
                            onChange={_onChange('glosses', glossesTransformer)}
                            required={true}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-speech">Speech</label>
                        <SpeechSelect
                            className="form-control"
                            name="ed-gloss-speech"
                            value={lexicalEntry.speechId}
                            onChange={_onChange('speechId')}
                            required={true}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-sources" className="control-label">Sources</label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-sources"
                            value={lexicalEntry.source}
                            onChange={_onChangeNative('source')}
                            required={true}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-comments">Comments</label>
                        <MarkdownInput name="ed-gloss-comments"
                            value={lexicalEntry.comments}
                            onChange={_onChange('comments')}
                        />
                    </div>
                </Panel>
            </div>
            <div className="col-sm-12 col-lg-6">
                <Panel title="Additional information">
                    <div className="checkbox">
                        <label>
                            <input type="checkbox"
                                name="ed-gloss-is-uncertain"
                                checked={lexicalEntry.isUncertain}
                                value={1}
                                onChange={_onChangeNative('isUncertain')}
                            /> Uncertain
                        </label>
                    </div>
                    <div className="checkbox">
                        <label>
                            <input type="checkbox"
                                name="ed-gloss-is-rejected"
                                checked={lexicalEntry.isRejected}
                                value={1}
                                onChange={_onChangeNative('isRejected')}
                            /> Rejected (strikethrough)
                        </label>
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-details">
                            Details
                            <OptionalLabel />
                        </label>
                        <LexicalEntryDetailInput name="ed-gloss-details"
                            onChange={_onChange('lexicalEntryDetails')}
                            value={lexicalEntry.lexicalEntryDetails} />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-keywords">
                            Keywords
                            <OptionalLabel />
                        </label>
                        <TagInput
                            name="ed-gloss-keywords"
                            value={lexicalEntry.keywords.map((k) => k.word)}
                            onChange={_onChange('keywords', keywordsTransformer)}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-tengwar">
                            Transcription
                            <OptionalLabel />
                        </label>
                        <TengwarInput
                            inputSize="sm"
                            languageId={lexicalEntry.languageId}
                            name="ed-gloss-tengwar"
                            onChange={_onChange('tengwar')}
                            originalText={lexicalEntry.word.word}
                            value={lexicalEntry.tengwar}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-account">Account</label>
                        <AccountSelect
                            name="ed-gloss-account"
                            onChange={_onChange('account')}
                            value={lexicalEntry.account}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-speech">Categorization</label>
                        <GlossGroupSelect
                            className="form-control"
                            name="ed-gloss-group-id"
                            onChange={_onChange('lexicalEntryGroupId')}
                            value={lexicalEntry.lexicalEntryGroupId}
                        />
                    </div>
                    <div className="form-group">
                        <label htmlFor="ed-gloss-label">
                            Label
                            <OptionalLabel />
                        </label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-label"
                            value={lexicalEntry.label}
                            onChange={_onChangeNative('label')}
                            required={false}
                        />
                    </div>
                </Panel>
            </div>
        </div>
    </>;
}

export default LexicalEntryForm;
