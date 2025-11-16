
import HtmlInject from '@root/components/HtmlInject';
import LexicalEntryDetail from './LexicalEntryDetail';
import type { IProps } from './LexicalEntryDetails._types';

import './LexicalEntryDetails.scss';

const LexicalEntryDetails = (props: IProps) => {
    const {
        lexicalEntry,
        onReferenceLinkClick,
        showDetails = true,
    } = props;
    return <>
        <HtmlInject html={lexicalEntry.comments} onReferenceLinkClick={onReferenceLinkClick} />
        {showDetails && lexicalEntry.lexicalEntryDetails.map(
            (d) => <LexicalEntryDetail key={`${d.order}_${d.category}`} detail={d} onReferenceLinkClick={onReferenceLinkClick} />,
        )}
    </>;
};

export default LexicalEntryDetails;
