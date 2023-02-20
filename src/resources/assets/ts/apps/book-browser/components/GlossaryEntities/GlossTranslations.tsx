import Tengwar from '@root/components/Tengwar';
import { IProps } from './GlossTranslations._types';

const GlossTranslations = (props: IProps) => {
    const {
        gloss,
    } = props;

    return <p>
        <span title={gloss.language.name}>{gloss.language.shortName.toLocaleUpperCase()}.</span>
        {' '}
        <Tengwar text={gloss.tengwar} />
        {' '}
        {gloss.type && <span className="word-type">{gloss.type}.</span>}
        {' '}
        <span itemProp="keywords">
            {gloss.allTranslations}
        </span>
    </p>;
};

export default GlossTranslations;
