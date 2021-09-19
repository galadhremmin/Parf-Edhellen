import React from 'react';

import { IProps } from './DiscussTable._types';
import DiscussTableRow from './DiscussTableRow';

function DiscussTable(props: IProps) {
    const {
        threads,
    } = props;
    return <div className="discuss-table">
        {threads.map((thread) => <DiscussTableRow thread={thread} key={thread.id} />)}
    </div>;
}

export default DiscussTable;
