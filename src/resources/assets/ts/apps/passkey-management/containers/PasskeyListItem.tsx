import { useState } from 'react';
import StaticAlert from '@root/components/StaticAlert';
import Dialog from '@root/components/Dialog';
import { fireEvent } from '@root/components/Component';
import type { IProps } from './PasskeyListItem._types';

// What the authenticator reported about how it connects, in words people recognise.
const TransportNames: Record<string, string> = {
    internal: 'This device',
    hybrid: 'Phone',
    usb: 'Security key',
    nfc: 'Security key',
    ble: 'Security key',
};

const describeTransport = (transport: string | null | undefined) => {
    const names = (transport ?? '').split(',')
        .map((t) => TransportNames[t.trim()])
        .filter((name) => name !== undefined);

    return names[0] ?? 'Passkey';
};

const PasskeyListItem = (props: IProps) => {
    const { passkey, onDeleted, passkeyApi } = props;
    const [error, setError] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);
    const [deletePassword, setDeletePassword] = useState('');

    const handleDeleteClick = () => {
        setShowDeleteDialog(true);
        setDeletePassword('');
        setError(null);
    };

    const handleDeleteConfirm = async () => {
        if (! deletePassword.trim()) {
            setError('Please enter your password');
            return;
        }

        try {
            setLoading(true);
            setError(null);

            if (! passkeyApi) {
                throw new Error('API not available');
            }

            await passkeyApi.deletePasskey(passkey.id, deletePassword);
            setShowDeleteDialog(false);
            setDeletePassword('');
            void fireEvent('PasskeyListItem', onDeleted);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const handleDeleteDialogDismiss = () => {
        setShowDeleteDialog(false);
        setDeletePassword('');
        setError(null);
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    return (
        <li className="ed-list__item PasskeyListItem">
            <span className="ed-list__body">
                <span className="ed-list__kind">{describeTransport(passkey.transport)}</span>
                <span className="ed-list__name">{passkey.displayName}</span>
                <span className="ed-ui">
                    Added {formatDate(passkey.createdAt)}
                    {' · '}
                    {passkey.lastUsedAt ? `last used ${formatDate(passkey.lastUsedAt)}` : 'not used yet'}
                </span>
                {error && ! showDeleteDialog && (
                    <span className="text-danger ed-ui">{error}</span>
                )}
            </span>

            <span className="ed-list__status">
                <button
                    className="btn btn-link btn-sm p-0 PasskeyListItem__remove"
                    onClick={handleDeleteClick}
                    disabled={loading}
                >
                    Remove
                </button>
            </span>

            <Dialog<string>
                open={showDeleteDialog}
                title="Remove passkey"
                confirmButtonText="Remove"
                cancelButtonText="Cancel"
                onDismiss={handleDeleteDialogDismiss}
                onConfirm={handleDeleteConfirm}
                valid={deletePassword.trim().length > 0}
            >
                <p>
                    Enter your password to remove this passkey.
                    {passkey.displayName && ` You won't be able to sign in with "${passkey.displayName}" again.`}
                </p>
                <div className="form-group">
                    <label htmlFor="delete-password" className="form-label">
                        Password
                    </label>
                    <form method="post" action="#">
                        <input
                            id="delete-password"
                            type="password"
                            className="form-control"
                            value={deletePassword}
                            onChange={(e) => setDeletePassword(e.target.value)}
                            autoComplete="current-password"
                            autoFocus
                            disabled={loading}
                            placeholder="Enter your password"
                        />
                        {error && (
                            <div className="text-danger mt-2">
                                <small>{error}</small>
                            </div>
                        )}
                    </form>
                </div>
            </Dialog>
        </li>
    );
};

export default PasskeyListItem;
