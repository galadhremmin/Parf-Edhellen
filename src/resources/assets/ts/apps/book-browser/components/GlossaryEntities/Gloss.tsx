import classNames from 'classnames';
import { IProps } from './Gloss._types';

import GlossDetails from './GlossDetails';
import GlossFooter from './GlossFooter';
import GlossInflections from './GlossInflections';
import GlossTitle from './GlossTitle';
import GlossTranslations from './GlossTranslations';
import OldVersionAlert from './OldVersionAlert';

import './Gloss.scss';

function Gloss(props: IProps) {
    const {
        bordered = true,
        gloss,
        onReferenceLinkClick,
        toolbar = true,
        warnings = true,
    } = props;

    const id = `gloss-block-${gloss.id}`;
    const className = classNames({
        contribution: !gloss.isCanon,
        'shadow-sm': bordered,
        border: bordered,
        rounded: bordered,
    }, 'gloss');

    return <blockquote itemScope={true} itemType="http://schema.org/Article" id={id} className={className}>
        {warnings && <OldVersionAlert gloss={gloss} />}
        <GlossTitle gloss={gloss} toolbar={toolbar} />
        <GlossTranslations gloss={gloss} />
        <GlossDetails gloss={gloss} showDetails={true} onReferenceLinkClick={onReferenceLinkClick} />
        <GlossInflections gloss={gloss} />
        <GlossFooter gloss={gloss} />
    </blockquote>;
}

export default Gloss;
