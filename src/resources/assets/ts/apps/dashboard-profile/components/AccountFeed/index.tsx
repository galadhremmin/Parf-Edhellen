import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import { useEffect, useState } from 'react';
import { IProps } from './index._types';

export default function AccountFeed({
    account,
    accountApi = resolve(DI.AccountApi),
}: IProps) {
    const [ feed, setFeed ] = useState([]);
    const [ cursor, setCursor ] = useState<string | null>(null);

    useEffect(() => {
        accountApi?.getFeed({
            accountId: account.id,
        }).then((feed) => {
            setFeed(feed.data);
            setCursor(feed.nextCursor);
        });
    }, [account.id]);

    return <pre>
        {JSON.stringify(feed, undefined, 2)}
    </pre>;
}
