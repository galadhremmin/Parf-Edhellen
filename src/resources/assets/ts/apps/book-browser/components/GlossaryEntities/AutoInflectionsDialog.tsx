import React, { useCallback, useState } from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import Spinner from '@root/components/Spinner';
import StaticAlert from '@root/components/StaticAlert';
import Tengwar from '@root/components/Tengwar';
import TextIcon from '@root/components/TextIcon';

import { IAutoInflectionsResponse } from '@root/connectors/backend/IInflectionResourceApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import { IProps } from './AutoInflectionsDialog._types';
import { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';

export function isEligibleForAutoInflections(lexicalEntry: ILexicalEntryEntity): boolean {
    return lexicalEntry?.type === 'verb' && //
        ['quenya', /*'sindarin'*/].includes(lexicalEntry?.language?.name.toLowerCase());
}

const AutoInflectionsDialog = (props: IProps) => {
    const {
        lexicalEntryId, 
        open, 
        onDismiss, 
        inflectionApi,
    } = props;

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [inflectionData, setInflectionData] = useState<IAutoInflectionsResponse | null>(null);
    const [thirdPartyUrl, setThirdPartyUrl] = useState<string | null>(null);

    const _fetchInflections = useCallback(async () => {
        setLoading(true);
        setError(null);
        setInflectionData(null);
        setThirdPartyUrl(null);

        try {
            const inflectData = await inflectionApi.autoInflections({ lexicalEntryId });
            setInflectionData(inflectData);
            setThirdPartyUrl(inflectData.url);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An unknown error occurred');
        } finally {
            setLoading(false);
        }
    }, [lexicalEntryId, inflectionApi]);

    // Fetch data when dialog opens
    React.useEffect(() => {
        if (open && lexicalEntryId) {
            _fetchInflections();
        }
    }, [open, lexicalEntryId, _fetchInflections]);

    const _onDialogDismiss = useCallback((e: React.MouseEvent<HTMLAnchorElement> | IComponentEvent<void>) => {
        if (typeof (e as React.MouseEvent<HTMLAnchorElement>).preventDefault === 'function') {
            (e as React.MouseEvent<HTMLAnchorElement>).preventDefault();
        }
        onDismiss();
    }, [onDismiss]);

    return (
        <Dialog
            open={open}
            onDismiss={_onDialogDismiss}
            title="Auto-generated Inflections"
            actionBar={false}
            size="lg"
        >
            {loading && (
                <StaticAlert type="info" className="d-flex align-items-center gap-2">
                    <Spinner />
                    <span>Fetching inflections from Quettali...</span>
                </StaticAlert>
            )}
            
            {error && (
                <StaticAlert type="warning">
                    <TextIcon icon="warning-sign" />
                    {error}
                </StaticAlert>
            )}

            {inflectionData && inflectionData.words.length === 0 && (
                <StaticAlert type="info" className="mt-0">
                    <TextIcon icon="info-sign" />{' '}
                    No inflections found for this word in Quettali.
                </StaticAlert>
            )}
            
            {inflectionData && inflectionData.words.length > 0 && (
                <div>
                    <StaticAlert type="info" className="mt-0">
                        <TextIcon icon="info-sign" />{' '}
                        These inflections were generated using grammatical rules and patterns observed throughout the corpus, but they may not be 100% accurate. 
                        Some forms may be attested, others may be neologisms, and others may be simply incorrect.
                    </StaticAlert>
                    {inflectionData.words.map((wordData) => (
                        <div key={wordData.qwid}>
                            <table className="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Part</th>
                                        <th>Inflected form</th>
                                        <th>Tengwar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {wordData.forms.map((form, idx) => (
                                        <tr key={idx}>
                                            <td>{form.tag}</td>
                                            <td>{form.forms.map((f) => <div key={f}>{f}</div>)}</td>
                                            <td>{inflectionData.tengwarMode ?
                                                form.forms.map((f) => <div key={f}><Tengwar text={f} mode={inflectionData.tengwarMode} transcribe={true} /></div>) : null}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colSpan={3} className="text-end">
                                            {thirdPartyUrl && <a className="btn btn-secondary" href={thirdPartyUrl} target="_blank" rel="noreferrer">
                                                More on 3<sup>rd</sup> party
                                            </a>}
                                            <a className="btn btn-primary ms-2" href="#" onClick={_onDialogDismiss}>
                                                Close
                                            </a>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    ))}
                </div>
            )}
        </Dialog>
    );
};

export default withPropInjection(AutoInflectionsDialog, {
    inflectionApi: DI.InflectionApi,
});
