import React, {
    useCallback,
    useState,
} from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import AccountSelect from '@root/components/AccountSelect';
import { fireEvent } from '@root/components/Component';
import TagInput from '@root/components/TagInput';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

import { RootReducer } from '../reducers';
import { IProps } from './GlossForm._types';

function GlossForm(props: IProps) {
    const [ account, setAccount ] = useState<IAccountSuggestion>(null);
    const [ tags, setTags ] = useState<string[]>([]);

    const {
        gloss,
        onSubmit,
    } = props;

    const _onSubmit = useCallback((ev: React.FormEvent) => {
        ev.preventDefault();
        fireEvent('GlossForm', onSubmit, null);
    }, [ onSubmit ]);

    return <form onSubmit={_onSubmit}>
        <div className="form-group">
            <label htmlFor="ed-gloss-word" className="control-label">Word</label>
            <input type="text" className="form-control" id="ed-gloss-word" name="word"
                value={gloss.word} onChange={null} />
        </div>
        <div className="form-group">
            <label htmlFor="translations">Translations</label>
            <TagInput name="translations" value={tags} onChange={(e) => setTags(e.value)} />
        </div>
        <div className="form-group">
            <label htmlFor="account">Account</label>
            <AccountSelect name="account" onChange={(e) => setAccount(e.value)} value={account} />
        </div>
        <div className="form-group">
            <label htmlFor="exampleInputEmail1">EDiscussmail address</label>
            <input type="email" className="form-control" id="exampleInputEmail1" placeholder="Email" />
        </div>
        <div className="form-group">
            <label htmlFor="exampleInputPassword1">Password</label>
            <input type="password" className="form-control" id="exampleInputPassword1" placeholder="Password" />
        </div>
        <div className="form-group">
            <label htmlFor="exampleInputFile">File input</label>
            <input type="file" id="exampleInputFile" />
            <p className="help-block">Example block-level help text here.</p>
        </div>
        <div className="checkbox">
            <label>
                <input type="checkbox" /> Check me out
            </label>
        </div>
        <button type="submit" className="btn btn-default">Submit</button>
    </form>;
}

const mapStateToProps = (state: RootReducer) => ({
    gloss: state.gloss,
} as Partial<IProps>);

// const actions = new DiscussActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({
    onSubmit: (ev) => console.log(ev),
} as Partial<IProps>);

export default connect(mapStateToProps, mapDispatchToProps)(GlossForm);
