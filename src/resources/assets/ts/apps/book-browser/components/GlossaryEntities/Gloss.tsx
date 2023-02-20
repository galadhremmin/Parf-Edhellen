import { Component } from 'react';
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
        bordered,
        gloss,
        onReferenceLinkClick,
        toolbar,
        warnings,
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

Gloss.defaultProps = {
    bordered: true,
    toolbar: true,
    warnings: true,
};

export default Gloss;
