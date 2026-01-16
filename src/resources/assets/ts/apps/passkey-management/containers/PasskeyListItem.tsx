import { useState } from 'react';
import StaticAlert from '@root/components/StaticAlert';
import Dialog from '@root/components/Dialog';
import { fireEvent } from '@root/components/Component';
import './PasskeyListItem.scss';
import type { IProps } from './PasskeyListItem._types';

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
        <div className="PasskeyListItem">
            {error && (
                <StaticAlert type="danger">
                    <strong>Error:</strong> {error}
                </StaticAlert>
            )}

            <div className="PasskeyListItem__content">
                <div className="PasskeyListItem__header">
                    <h4 className="PasskeyListItem__name">{passkey.displayName}</h4>
                </div>

                <div className="PasskeyListItem__metadata">
                    <p>
                        <strong>Created:</strong> {formatDate(passkey.createdAt)}
                    </p>
                    {passkey.lastUsedAt && (
                        <p>
                            <strong>Last used:</strong> {formatDate(passkey.lastUsedAt)}
                        </p>
                    )}
                </div>
            </div>

            <div className="PasskeyListItem__actions">
                {passkey.transport && <span className="PasskeyListItem__transport">{passkey.transport}</span>}
                <button
                    className="btn btn-sm btn-danger"
                    onClick={handleDeleteClick}
                    disabled={loading}
                >
                    Delete
                </button>
            </div>

            <Dialog<string>
                open={showDeleteDialog}
                title="Delete Passkey"
                confirmButtonText="Delete"
                cancelButtonText="Cancel"
                onDismiss={handleDeleteDialogDismiss}
                onConfirm={handleDeleteConfirm}
                valid={deletePassword.trim().length > 0}
            >
                <p>
                    For security purposes, please enter your password to confirm deletion of this passkey.
                    {passkey.displayName && ` This will permanently delete "${passkey.displayName}".`}
                </p>
                <div className="form-group">
                    <label htmlFor="delete-password" className="form-label">
                        Password:
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
        </div>
    );
};

export default PasskeyListItem;
