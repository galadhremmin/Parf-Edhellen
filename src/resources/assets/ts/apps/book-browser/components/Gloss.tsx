import classNames from 'classnames';
import React from 'react';

import { IProps } from './Gloss._types';

import GlossDetails from './GlossDetails';
import GlossFooter from './GlossFooter';
import GlossInflections from './GlossInflections';
import GlossTitle from './GlossTitle';
import GlossTranslations from './GlossTranslations';
import OldVersionAlert from './OldVersionAlert';

import './Gloss.scss';

export default class Gloss extends React.Component<IProps> {
    public static defaultProps = {
        toolbar: true,
        warnings: true,
    };

    public render() {
        const {
            gloss,
            onReferenceLinkClick,
            toolbar,
            warnings,
        } = this.props;

        const id = `gloss-block-${gloss.id}`;
        const className = classNames({ contribution: !gloss.isCanon }, 'gloss');

        return <blockquote itemScope={true} itemType="http://schema.org/Article" id={id} className={className}>
            {warnings && <OldVersionAlert gloss={gloss} />}
            <GlossTitle gloss={gloss} toolbar={toolbar} />
            <GlossTranslations gloss={gloss} />
            <GlossDetails gloss={gloss} showDetails={true} onReferenceLinkClick={onReferenceLinkClick} />
            <GlossInflections gloss={gloss} />
            <GlossFooter gloss={gloss} />
        </blockquote>;
    }
}
