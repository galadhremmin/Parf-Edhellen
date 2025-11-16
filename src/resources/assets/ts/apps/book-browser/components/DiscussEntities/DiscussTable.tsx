import type { IProps } from './DiscussTable._types';
import DiscussTableRow from './DiscussTableRow';

function DiscussTable(props: IProps) {
    const {
        threads,
    } = props;
    return <div className="discuss-table shadow mb-3">
        {threads.map((thread) => <DiscussTableRow thread={thread} key={thread.id} />)}
    </div>;
}

export default DiscussTable;
