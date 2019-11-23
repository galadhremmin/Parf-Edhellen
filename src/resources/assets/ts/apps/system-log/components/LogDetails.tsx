import React from 'react';

import ProfileLink from '@root/components/ProfileLink';
import { IProps } from './LogDetails._types';

import './LogDetails.scss';

function LogDetails(props: IProps) {
    const {
        log,
    } = props;

    const account = log.accountId ? {
        id: log.accountId,
        nickname: String(log.accountId),
    } : null;

    return <>
        <div className="LogDetails__stack">
            {log.error}
        </div>
        <table className="table table-condensed">
            <thead>
                <tr>
                    <th>Account ID</th>
                    <th>IP</th>
                    <th>ID</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><ProfileLink account={account} /></td>
                    <td>{log.ip}</td>
                    <td>{log.id}</td>
                </tr>
            </tbody>
        </table>
    </>;
}

export default LogDetails;
