import React, { useState } from 'react';

import DateLabel from '@root/components/DateLabel';
import Pagination from '@root/components/Pagination';
import { IErrorEntity } from '@root/connectors/backend/ILogApi';
import LogDetails from './LogDetails';
import { IProps } from './LogList._types';

import './LogList.scss';

function LogList(props: IProps) {
    const {
        currentPage,
        logs,
        noOfPages,
        onClick,
    } = props;

    const [ idOpen, setIdOpen ] = useState<number>(0);

    const _onOpen = (id: number) => () => {
        setIdOpen(id === idOpen ? 0 : id);
    };

    return <>
        <table className="LogList table table-striped table-condensed table-bordered table-hover">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Datetime</th>
                    <th>Message</th>
                </tr>
            </thead>
            {(logs || []).map((log: IErrorEntity) => <tbody key={log.id}>
                <tr onClick={_onOpen(log.id)}>
                    <td>{log.category}</td>
                    <td><DateLabel dateTime={log.createdAt} /></td>
                    <td>{log.message}</td>
                </tr>
                {idOpen === log.id && <tr>
                    <td colSpan={3}>
                        <LogDetails log={log} />
                    </td>
                </tr>}
            </tbody>)}
        </table>
        <Pagination currentPage={currentPage}
                        noOfPages={noOfPages}
                        onClick={onClick}
        />
    </>;
}

export default LogList;
