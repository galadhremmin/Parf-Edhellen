import { useState } from 'react';

import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import type IUtilityApi from '@root/connectors/backend/IUtilityApi';
import type IRoleManager from '@root/security/IRoleManager';

interface IProps {
    word: string;
    roleManager?: IRoleManager;
    utilityApi?: IUtilityApi;
}

function GlossaryEntitiesEmpty({ word, roleManager, utilityApi }: IProps) {
    const [reported, setReported] = useState(false);

    const onReport = async () => {
        if (reported || !utilityApi) return;
        await utilityApi.reportMissingWord(word);
        setReported(true);
    };

    const isAnonymous = roleManager?.isAnonymous ?? true;
    const contributeUrl = `/contribute/contribution/create/lexical_entry?word=${encodeURIComponent(word)}`;
    const actionUrl = isAnonymous
        ? `/login?redirect=${encodeURIComponent(contributeUrl)}`
        : contributeUrl;

    return <div className="GlossaryEntitiesEmpty text-center py-5">
        <div style={{ fontSize: '6rem', lineHeight: 1, marginBottom: '1.5rem', opacity: 0.5 }}>
            <TextIcon icon="emoji-dizzy" />
        </div>
        <h3>Alas! Not found in the archives.</h3>
        <p className="text-muted">
            The word <em>{word}</em> does not appear to exist in the dictionary.
        </p>
        <div className="mt-4 d-flex flex-column align-items-center gap-3">
            <a href={actionUrl} className="btn btn-primary">
                <TextIcon icon="edit" />{' '}Add <em>{word}</em> to the dictionary
            </a>
            {reported
                ? <span className="text-muted small">Thank you — we have noted it!</span>
                : <button type="button" className="btn btn-link btn-sm text-muted" onClick={onReport}>
                    Think this word should exist? Let us know.
                </button>
            }
        </div>
    </div>;
}

export default withPropInjection(GlossaryEntitiesEmpty, {
    roleManager: DI.RoleManager,
    utilityApi: DI.UtilityApi,
});
