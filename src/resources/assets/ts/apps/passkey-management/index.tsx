import { useCallback, useEffect, useState, useRef } from 'react';
import Panel from '@root/components/Panel';
import Dialog from '@root/components/Dialog';
import Spinner from '@root/components/Spinner';
import StaticAlert from '@root/components/StaticAlert';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import registerApp from '../app';
import type { IProps, IPasskey } from './index._types';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';
import PasskeyList from './containers/PasskeyList';
import AddPasskeyForm from './containers/AddPasskeyForm';

interface IPasskeyManagementProps extends IProps {
    passkeyApi?: IPasskeyApi;
}

const PasskeyManagement = (props: IPasskeyManagementProps) => {
    const { account, passkeyApi } = props;

    const [passkeys, setPasskeys] = useState<IPasskey[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showAddForm, setShowAddForm] = useState(false);
    const [canSubmitForm, setCanSubmitForm] = useState(false);
    const formRef = useRef<HTMLFormElement>(null);

    // Load passkeys
    const loadPasskeys = useCallback(async () => {
        if (! passkeyApi) {
            setError('API not available');
            return;
        }

        try {
            setLoading(true);
            setError(null);

            const data = await passkeyApi.getPasskeys();
            setPasskeys(data.passkeys || []);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setLoading(false);
        }
    }, [passkeyApi]);

    useEffect(() => {
        loadPasskeys();
    }, [loadPasskeys]);

    return (
        <Panel
            title="Passkeys"
            className="PasskeyManagement"
            shadow={true}
        >
            {error && (
                <StaticAlert
                    type="danger"
                >
                    <strong>Error:</strong> {error}
                </StaticAlert>
            )}

            {loading ? (
                <Spinner />
            ) : (
                <>
                    <PasskeyList
                        passkeys={passkeys}
                        onPasskeyDeleted={loadPasskeys}
                        passkeyApi={passkeyApi}
                    />

                    <div className="PasskeyManagement__actions m-2 text-center">
                        {! showAddForm && (
                            <button
                                className="btn btn-primary"
                                onClick={() => setShowAddForm(true)}
                            >
                                Create Passkey
                            </button>
                        )}

                        {showAddForm && (
                            <Dialog
                                title="Create Passkey"
                                open={true}
                                confirmButtonText="Start Registration"
                                cancelButtonText="Close"
                                onDismiss={() => {
                                    setShowAddForm(false);
                                    setCanSubmitForm(false);
                                }}
                                onConfirm={() => {
                                    formRef.current?.requestSubmit();
                                }}
                                valid={canSubmitForm}
                            >
                                <AddPasskeyForm
                                    formRef={formRef}
                                    account={account}
                                    passkeyApi={passkeyApi}
                                    existingPasskeys={passkeys}
                                    onValidationChange={setCanSubmitForm}
                                    onSuccess={() => {
                                        setShowAddForm(false);
                                        setCanSubmitForm(false);
                                        loadPasskeys();
                                    }}
                                    onCancel={() => {
                                        setShowAddForm(false);
                                        setCanSubmitForm(false);
                                    }}
                                />
                            </Dialog>
                        )}
                    </div>
                </>
            )}
        </Panel>
    );
};

const PasskeyManagementWithDI = withPropInjection(PasskeyManagement, {
    passkeyApi: DI.PasskeyApi,
});

export default registerApp(PasskeyManagementWithDI);
