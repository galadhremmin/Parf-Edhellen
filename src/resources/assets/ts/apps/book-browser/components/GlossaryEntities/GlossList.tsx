import Tengwar from '@root/components/Tengwar';
import type { IProps } from './GlossList._types';

const GlossList = (props: IProps) => {
    const {
        lexicalEntry,
    } = props;

    return <p className="lexical-entry__gloss-list">
        <span className="word-language" title={lexicalEntry.language.name}>{lexicalEntry.language.shortName.toLocaleUpperCase()}.</span>
        {' '}
        <Tengwar text={lexicalEntry.tengwar} />
        {' '}
        {lexicalEntry.type && <span className="word-type">{lexicalEntry.type}.</span>}
        {' '}
        <span itemProp="keywords">
            {lexicalEntry.allGlosses}
        </span>
    </p>;
};

export default GlossList;
