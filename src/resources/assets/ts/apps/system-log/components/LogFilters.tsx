import {
    useCallback,
    useEffect,
    useState,
} from 'react';
import type { MouseEvent } from 'react';

import AccountSelect from '@root/components/Form/AccountSelect';
import type { IComponentEvent } from '@root/components/Component._types';
import type { IAccountSuggestion } from '@root/connectors/backend/IAccountApi';
import type { IAccountIpAddress } from '@root/connectors/backend/ILogApi';
import { formatDateTimeShortWithSeconds } from '@root/utilities/DateTime';

import type { IProps } from './LogFilters._types';

import './LogFilters.scss';

/**
 * Presents the filtering options for the exception log: an account, and -- once an account has
 * been selected -- the IP addresses that account is known to have used. Filtering by an account
 * matches every exception logged for the account _or_ for any of its IP addresses, which makes it
 * possible to find anonymous exceptions belonging to the same person.
 */
function LogFilters({ logApi, filter, onChange }: IProps) {
    const { account, ip } = filter;
    const accountId = account?.id ?? null;

    const [ipAddresses, setIpAddresses] = useState<IAccountIpAddress[]>([]);
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        if (accountId === null) {
            setIpAddresses([]);
            return undefined;
        }

        let cancelled = false;
        setLoading(true);

        logApi.getAccountIpHistory(accountId)
            .then((response) => {
                if (! cancelled) {
                    setIpAddresses(response.ipAddresses ?? []);
                }
            })
            .catch((e) => {
                console.error(`Failed to load IP history: ${e}`);
                if (! cancelled) {
                    setIpAddresses([]);
                }
            })
            .finally(() => {
                if (! cancelled) {
                    setLoading(false);
                }
            });

        return () => {
            cancelled = true;
        };
    }, [logApi, accountId]);

    const onAccountChange = useCallback((ev: IComponentEvent<IAccountSuggestion>) => {
        onChange({
            account: ev.value ?? null,
            ip: null,
        });
    }, [onChange]);

    const onIpClick = useCallback((ev: MouseEvent<HTMLButtonElement>) => {
        const value = ev.currentTarget.dataset.ip ?? null;
        onChange({
            account,
            ip: value,
        });
    }, [account, onChange]);

    const onClear = useCallback(() => {
        onChange({
            account: null,
            ip: null,
        });
    }, [onChange]);

    const numberOfOccurrences = ipAddresses.reduce((sum, address) => sum + address.numberOfOccurrences, 0);

    return <div className="LogFilters">
        <div className="LogFilters__group">
            <label className="LogFilters__label" htmlFor="ed-log-account">Account</label>
            <AccountSelect
                name="ed-log-account"
                onChange={onAccountChange}
                value={account ?? undefined}
            />
        </div>
        {account !== null && <div className="LogFilters__group">
            <span className="LogFilters__label">IP history</span>
            {loading && <span className="LogFilters__hint">Loading&hellip;</span>}
            {! loading && ipAddresses.length === 0 && <span className="LogFilters__hint">
                No IP addresses on record for this account.
            </span>}
            {! loading && ipAddresses.length > 0 && <div className="LogFilters__options">
                <button
                    className={`btn btn-sm ${ip === null ? 'btn-primary' : 'btn-outline-secondary'}`}
                    onClick={onIpClick}
                    type="button">
                    All addresses
                    <span className="LogFilters__count">{numberOfOccurrences}</span>
                </button>
                {ipAddresses.map((address) => <button
                    className={`btn btn-sm ${ip === address.ip ? 'btn-primary' : 'btn-outline-secondary'}`}
                    data-ip={address.ip}
                    key={address.ip}
                    onClick={onIpClick}
                    title={`${address.sources.join(', ')}${address.lastSeen !== null
                        ? `; last seen ${formatDateTimeShortWithSeconds(address.lastSeen)}`
                        : ''}`}
                    type="button">
                    {address.ip}
                    <span className="LogFilters__count">{address.numberOfOccurrences}</span>
                </button>)}
            </div>}
        </div>}
        {account !== null && <button
            className="btn btn-sm btn-secondary LogFilters__clear"
            onClick={onClear}
            type="button">
            Clear account filter
        </button>}
    </div>;
}

export default LogFilters;
