import React, {
    useCallback,
} from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import AccountSelect from '@root/components/Form/AccountSelect';
import LanguageSelect from '@root/components/Form/LanguageSelect';
import MarkdownInput from '@root/components/Form/MarkdownInput';
import OptionalLabel from '@root/components/Form/OptionalLabel';
import SpeechSelect from '@root/components/Form/SpeechSelect';
import TagInput from '@root/components/Form/TagInput';
import TengwarInput from '@root/components/Form/TengwarInput';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import Panel from '@root/components/Panel';

import GlossActions from '../actions/GlossActions';
import GlossDetailInput from '../components/GlossDetailInput';
import { RootReducer } from '../reducers';
import {
    defaultTransformer,
    keywordsTransformer,
    senseTransformer,
    translationsTransformer,
    wordTransformer,
} from '../utilities/value-transformers';
import { ValueTransformer } from '../utilities/value-transformers._types';
import {
    GlossProps,
    IProps,
} from './GlossForm._types';

function GlossForm(props: IProps) {
    const {
        confirmButton,
        edit,
        errors,
        name,
        onGlossFieldChange,
        onSubmit,
    } = props;

    const {
        gloss,
    } = props;

    const _onFieldChange = (field: GlossProps, value: string) => {
        const params = {
            field,
            value,
        };

        fireEvent(name, onGlossFieldChange, params);
    };

    const _onChangeNative = (field: GlossProps, transform: ValueTransformer = defaultTransformer) =>
        (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = transform(e.target.type === 'checkbox' || e.target.type === 'radio'
            ? e.target.checked
            : e.target.value,
        );
        _onFieldChange(field, value);
    };

    const _onChange = (field: GlossProps, transform: ValueTransformer = defaultTransformer) =>
        (e: IComponentEvent<any>) => {
        const value = transform(e.value);
        _onFieldChange(field, value);
    };

    const _onSubmit = (ev: React.FormEvent) => {
        ev.preventDefault();

        // create a deep copy of the gloss.
        const newGloss = JSON.parse(JSON.stringify(gloss)) as typeof gloss;
        if (! edit) {
            newGloss.id = null;
        }

        // remove parameters that are not supported by the service
        delete newGloss.accountId; // replaced by `account`.
        delete newGloss.childGlossId;
        delete newGloss.hasDetails;
        delete newGloss.isDeleted;
        delete newGloss.isIndex;
        delete newGloss.isLatest;
        delete newGloss.originGlossId;
        delete newGloss.senseId; // replaced by `sense`.

        if (newGloss.tengwar.length < 1) {
            delete newGloss.tengwar;
        }

        fireEvent(name, onSubmit, newGloss);
    };

    return <form onSubmit={_onSubmit}>
        <ValidationErrorAlert error={errors} />
        <div className="row">
            <div className="col-sm-12 col-lg-6">
                <Panel title="Basic information">
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-word" className="control-label">Word</label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-word"
                            value={gloss.word.word}
                            onChange={_onChangeNative('word', wordTransformer)}
                            required={true}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-sense-word" className="control-label">Sense</label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-sense-word"
                            value={gloss.sense.word.word}
                            onChange={_onChangeNative('sense', senseTransformer)}
                            required={true}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-language">Language</label>
                        <LanguageSelect
                            className="form-control"
                            name="ed-gloss-language"
                            value={gloss.languageId}
                            onChange={_onChange('languageId')}
                            required={true}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-translations">Translations</label>
                        <TagInput
                            name="ed-gloss-translations"
                            value={gloss.translations.map((t) => t.translation)}
                            onChange={_onChange('translations', translationsTransformer)}
                            required={true}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-speech">Speech</label>
                        <SpeechSelect
                            className="form-control"
                            name="ed-gloss-speech"
                            value={gloss.speechId}
                            onChange={_onChange('speechId')}
                            required={true}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-sources" className="control-label">Sources</label>
                        <input type="text"
                            className="form-control"
                            id="ed-gloss-sources"
                            value={gloss.source}
                            onChange={_onChangeNative('source')}
                            required={true}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-comments">Comments</label>
                        <MarkdownInput name="ed-gloss-comments"
                            value={gloss.comments}
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
                                checked={gloss.isUncertain}
                                value={1}
                                onChange={_onChangeNative('isUncertain')}
                            /> Uncertain
                        </label>
                    </div>
                    <div className="checkbox">
                        <label>
                            <input type="checkbox"
                                name="ed-gloss-is-rejected"
                                checked={gloss.isRejected}
                                value={1}
                                onChange={_onChangeNative('isRejected')}
                            /> Rejected (strikethrough)
                        </label>
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-details">
                            Details
                            <OptionalLabel />
                        </label>
                        <GlossDetailInput name="ed-gloss-details"
                            onChange={_onChange('glossDetails')}
                            value={gloss.glossDetails} />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-keywords">
                            Keywords
                            <OptionalLabel />
                        </label>
                        <TagInput
                            name="ed-gloss-keywords"
                            value={gloss.keywords.map((k) => k.word)}
                            onChange={_onChange('keywords', keywordsTransformer)}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-tengwar">
                            Transcription
                            <OptionalLabel />
                        </label>
                        <TengwarInput
                            name="ed-gloss-tengwar"
                            onChange={_onChange('tengwar')}
                            value={gloss.tengwar}
                        />
                    </div>
                    <div className="form-group form-group-sm">
                        <label htmlFor="ed-gloss-account">Account</label>
                        <AccountSelect
                            name="ed-gloss-account"
                            onChange={_onChange('account')}
                            value={gloss.account}
                        />
                    </div>
                </Panel>
            </div>
        </div>
        <div className="text-center">
            <button type="submit" className="btn btn-primary">{confirmButton}</button>
        </div>
    </form>;
}

GlossForm.defaultProps = {
    confirmButton: 'Confirm and Save',
    edit: false,
    errors: null,
    gloss: null,
    name: 'GlossForm',
} as Partial<IProps>;

const mapStateToProps = (state: RootReducer) => ({
    errors: state.errors,
    gloss: state.gloss,
} as Partial<IProps>);

const actions = new GlossActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({
    onGlossFieldChange: (e) => dispatch(actions.setField(e.value.field, e.value.value)),
    onSubmit: (e) => dispatch(actions.saveGloss(e.value)),
} as Partial<IProps>);

export default connect(mapStateToProps, mapDispatchToProps)(GlossForm);
