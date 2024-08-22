import Spinner from '@root/components/Spinner';
import { IFeedRecord } from '@root/connectors/backend/IAccountApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { useEffect, useState } from 'react';
import { Waypoint } from 'react-waypoint';
import FeedUnit from './FeedUnit';
import { IProps } from './index._types';

import './AccountFeed.scss';

export default function AccountFeed({
    account,
    accountApi = resolve(DI.AccountApi),
}: IProps) {
    const [ loading, setLoading ] = useState(false);
    const [ restricted, setRestricted ] = useState(false);
    const [ feed, setFeed ] = useState<IFeedRecord[]>([]);
    const [ cursor, setCursor ] = useState<string | null>(null);

    function load() {
        if (loading || 
            /* EOF? */ (cursor === null && feed.length > 0)) {
            return;
        }

        setLoading(true);

        accountApi?.getFeed({
            accountId: account.id,
            cursor,
        }).then((response) => {
            if (response.restricted) {
                // the account has a restricted feed
                setRestricted(true);
                setFeed([]);
            } else {
                // Catch duplicate requests
                if (response.nextCursor !== cursor || response.nextCursor === null) {
                    setFeed((feed || []).concat(response.data));
                    setCursor(response.nextCursor);
                }
            }
        }).finally(() => {
            setLoading(false);
        });
    }

    function _onWaypointExposed(_: Waypoint.CallbackArgs) {
        if (feed.length > 0) {
            load();
        }
    }

    useEffect(() => {
        load();
    }, [account.id]);

    if (restricted) {
        return <div/>;
    }

    return <>
        <section className="account-feed">
            {feed.map(f => <FeedUnit unit={f} key={f.id} />)}
        </section>
        <Waypoint onEnter={_onWaypointExposed} />
        {loading && <div className="text-center">
            <Spinner />
        </div>}
    </>;
}
