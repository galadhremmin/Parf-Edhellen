
import LexicalEntryDetail from './LexicalEntryDetail';
import LexicalEntryDerivations from './LexicalEntryDerivations';
import LexicalEntryDerivatives from './LexicalEntryDerivatives';
import LexicalEntryPhoneticDevelopments from './LexicalEntryPhoneticDevelopments';
import type { IProps } from './LexicalEntryDetails._types';

import './LexicalEntryDetails.scss';

const LexicalEntryDetails = (props: IProps) => {
    const {
        lexicalEntry,
        onReferenceLinkClick,
        showDetails = true,
    } = props;

    const renderDetail = (d: typeof lexicalEntry.lexicalEntryDetails[number]) =>
        <LexicalEntryDetail key={`${d.order}_${d.category}`} detail={d} onReferenceLinkClick={onReferenceLinkClick} />;

    return <>
        {showDetails && <>
            {lexicalEntry.lexicalEntryDetails.map(renderDetail)}
            <LexicalEntryDerivatives derivatives={lexicalEntry.derivatives} />
            <LexicalEntryDerivations derivations={lexicalEntry.derivations} />
            <LexicalEntryPhoneticDevelopments
                derivations={lexicalEntry.derivations}
                phoneticDevelopments={lexicalEntry.phoneticDevelopments}
                word={lexicalEntry.word}
            />
        </>}
    </>;
};

export default LexicalEntryDetails;
