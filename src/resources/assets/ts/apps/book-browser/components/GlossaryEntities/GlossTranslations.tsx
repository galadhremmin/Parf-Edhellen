import Tengwar from '@root/components/Tengwar';
import { IProps } from './GlossTranslations._types';

const GlossTranslations = (props: IProps) => {
    const {
        lexicalEntry,
    } = props;

    return <p>
        <span title={lexicalEntry.language.name}>{lexicalEntry.language.shortName.toLocaleUpperCase()}.</span>
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

export default GlossTranslations;
