import React, {
    useState,
} from 'react';

import AccountSelect from '@root/components/AccountSelect';
import TagInput from '@root/components/TagInput';
import { IAccountSuggestion } from '@root/connectors/backend/AccountApiConnector._types';

function GlossForm(props: any) {
    const [ account, setAccount ] = useState<IAccountSuggestion>(null);
    const [ tags, setTags ] = useState<string[]>([]);

    return <form>
        <div className="form-group">
            <label htmlFor="translations">Translations</label>
            <TagInput name="translations" value={tags} onChange={(e) => setTags(e.value)} />
        </div>
        <div className="form-group">
            <label htmlFor="account">Account</label>
            <AccountSelect name="account" onChange={(e) => setAccount(e.value)} value={account} />
        </div>
        <div className="form-group">
            <label htmlFor="exampleInputEmail1">Email address</label>
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

export default GlossForm;
