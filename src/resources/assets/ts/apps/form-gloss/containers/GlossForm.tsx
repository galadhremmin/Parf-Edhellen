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

import GlossActions from '../actions/GlossActions';
import { RootReducer } from '../reducers';
import {
    defaultTransformer,
    keywordsTransformer,
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
        account,
        comments,
        keywords,
        languageId,
        source,
        speechId,
        translations,
        word,
    } = props.gloss;

    const _onFieldChange = (field: GlossProps, value: string) => {
        const params = {
            field,
            value,
        };

        fireEvent(name, onGlossFieldChange, params);
    };

    const _onChangeNative = (field: GlossProps, transform: ValueTransformer = defaultTransformer) =>
        (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = transform(e.target.value);
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
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-word" className="control-label">Word</label>
            <input type="text"
                className="form-control"
                id="ed-gloss-word"
                value={word.word}
                onChange={_onChangeNative('word', wordTransformer)}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-language">Language</label>
            <LanguageSelect
                className="form-control"
                name="ed-gloss-language"
                value={languageId}
                onChange={_onChange('languageId')}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-translations">Translations</label>
            <TagInput
                name="ed-gloss-translations"
                value={translations.map((t) => t.translation)}
                onChange={_onChange('translations', translationsTransformer)}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-speech">Speech</label>
            <SpeechSelect
                className="form-control"
                name="ed-gloss-speech"
                value={speechId}
                onChange={_onChange('speechId')}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-sources" className="control-label">Sources</label>
            <input type="text"
                className="form-control"
                id="ed-gloss-sources"
                value={source}
                onChange={_onChangeNative('source')}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-keywords">
                Keywords
                <OptionalLabel />
            </label>
            <TagInput
                name="ed-gloss-keywords"
                value={keywords.map((k) => k.word)}
                onChange={_onChange('keywords', keywordsTransformer)}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-account">Account</label>
            <AccountSelect
                name="ed-gloss-account"
                onChange={_onChange('account')}
                value={account}
            />
        </div>
        <div className="form-group form-group-sm">
            <label htmlFor="ed-gloss-comments">Comments</label>
            <MarkdownInput name="ed-gloss-comments"
                value={comments}
                onChange={_onChange('comments')}
            />
        </div>
        <div className="checkbox">
            <label>
                <input type="checkbox" /> Check me out
            </label>
        </div>
        <button type="submit" className="btn btn-default">Submit</button>
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
