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

    const _onSubmit = useCallback((ev: React.FormEvent) => {
        ev.preventDefault();
        fireEvent(name, onSubmit, null);
    }, [ name, onSubmit ]);

    return <form onSubmit={_onSubmit}>
        <Panel title="Basic information">
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-word" className="control-label">Word</label>
                <input type="text"
                    className="form-control"
                    id="ed-gloss-word"
                    value={gloss.word.word}
                    onChange={_onChangeNative('word', wordTransformer)}
                />
            </div>
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-sense-word" className="control-label">Sense</label>
                <input type="text"
                    className="form-control"
                    id="ed-gloss-sense-word"
                    value={gloss.sense.word.word}
                    onChange={_onChangeNative('sense', senseTransformer)}
                />
            </div>
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-language">Language</label>
                <LanguageSelect
                    className="form-control"
                    name="ed-gloss-language"
                    value={gloss.languageId}
                    onChange={_onChange('languageId')}
                />
            </div>
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-translations">Translations</label>
                <TagInput
                    name="ed-gloss-translations"
                    value={gloss.translations.map((t) => t.translation)}
                    onChange={_onChange('translations', translationsTransformer)}
                />
            </div>
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-speech">Speech</label>
                <SpeechSelect
                    className="form-control"
                    name="ed-gloss-speech"
                    value={gloss.speechId}
                    onChange={_onChange('speechId')}
                />
            </div>
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-sources" className="control-label">Sources</label>
                <input type="text"
                    className="form-control"
                    id="ed-gloss-sources"
                    value={gloss.source}
                    onChange={_onChangeNative('source')}
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
        <Panel title="Additional information">
            <div className="form-group form-group-sm">
                <label htmlFor="ed-gloss-account">Account</label>
                <AccountSelect
                    name="ed-gloss-account"
                    onChange={_onChange('account')}
                    value={gloss.account}
                />
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
        </Panel>
        <div className="text-center">
            <button type="submit" className="btn btn-primary">Submit</button>
        </div>
    </form>;
}

GlossForm.defaultProps = {
    name: 'GlossForm',
} as Partial<IProps>;

const mapStateToProps = (state: RootReducer) => ({
    gloss: state.gloss,
} as Partial<IProps>);

const actions = new GlossActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({
    onGlossFieldChange: (e) => dispatch(actions.setField(e.value.field, e.value.value)),
    onSubmit: (e) => console.log(e),
} as Partial<IProps>);

export default connect(mapStateToProps, mapDispatchToProps)(GlossForm);
