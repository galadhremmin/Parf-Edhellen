import DateLabel from '@root/components/DateLabel';
import { IThreadEntity } from '@root/connectors/backend/IDiscussApi';
import React from 'react';
import { IEntitiesComponentProps } from '../../containers/Entities._types';

function DiscussEntities(props: IEntitiesComponentProps<IThreadEntity>) {
    const {
        sections,
        languages: groups,
    } = props;
    return groups.map((group) => <div key={group.id}>
        <h2>{group.name}</h2>
        <div className="discuss-table">
            {sections[group.id].map((thread) => <div className="r" key={thread.id}>
                <div className="c">
                    <a href="http://localhost:8000/author/1927-rainelda" title="View Rainelda's profile" className="pp">
                        <img src="/storage/avatars/1927.png" />
                    </a>
                </div>
                <div className="c p2">
                    <a href="?">{thread.subject}</a>
                    <div className="pi">
                        {thread.accountId} on <DateLabel dateTime={thread.updatedAt || thread.createdAt} />
                    </div>
                </div>
            </div>)}
        </div>
    </div>);
}

export default DiscussEntities;
