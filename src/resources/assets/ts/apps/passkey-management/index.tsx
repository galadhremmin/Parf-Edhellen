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
        void loadPasskeys();
    }, [loadPasskeys]);

    return (
        <Panel
            eyebrow="Signing in without a password"
            title="Passkeys"
            headingLevel={2}
            className="PasskeyManagement"
        >
            <p className="ed-panel__lead">
                Sign in with your fingerprint, face or a security key. A passkey never leaves your device,
                so there is nothing to forget, leak or phish.
            </p>

            {error && (
                <StaticAlert type="danger">
                    <strong>Error:</strong> {error}
                </StaticAlert>
            )}

            {loading ? (
                <Spinner />
            ) : (
                <>
                    <PasskeyList
                        passkeys={passkeys}
                        onPasskeyDeleted={() => void loadPasskeys()}
                        passkeyApi={passkeyApi}
                    />

                    <div className="ed-panel__footer">
                        <p className="ed-panel__note">
                            {passkeys.length === 0
                                ? 'You haven\'t added a passkey yet.'
                                : 'Add one for each device you sign in from.'}
                        </p>
                        <button
                            className="btn btn-secondary"
                            onClick={() => setShowAddForm(true)}
                            disabled={showAddForm}
                        >
                            Add a passkey
                        </button>
                    </div>

                    {showAddForm && (
                        <Dialog
                            title="Add a passkey"
                            open={true}
                            confirmButtonText="Start registration"
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
                                onValidationChange={(ev) => setCanSubmitForm(ev.value)}
                                onSuccess={() => {
                                    setShowAddForm(false);
                                    setCanSubmitForm(false);
                                    void loadPasskeys();
                                }}
                                onCancel={() => {
                                    setShowAddForm(false);
                                    setCanSubmitForm(false);
                                }}
                            />
                        </Dialog>
                    )}
                </>
            )}
        </Panel>
    );
};

const PasskeyManagementWithDI = withPropInjection(PasskeyManagement, {
    passkeyApi: DI.PasskeyApi,
});

export default registerApp(PasskeyManagementWithDI);
