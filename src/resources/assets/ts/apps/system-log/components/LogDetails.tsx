import ProfileLink from '@root/components/ProfileLink';
import type { IProps } from './LogDetails._types';

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
        <div className="table-responsive">
            <table className="table table-condensed">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>User agent</th>
                        <th>Account ID</th>
                        <th>IP</th>
                        <th>ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{log.url}</td>
                        <td>{log.userAgent}</td>
                        <td><ProfileLink account={account} /></td>
                        <td>{log.ip}</td>
                        <td>{log.id}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </>;
}

export default LogDetails;
