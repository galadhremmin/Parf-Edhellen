import classNames from 'classnames';
import React, { Suspense } from 'react';

import { DI, resolve } from '@root/di';
import {
    SecurityRole,
} from '@root/security';

import { IProps } from './GlossTitle._types';

import GlossAbsoluteLink from './GlossAbsoluteLink';
import NeologismIndicator from './NeologismIndicator';
import NumberOfComments from './NumberOfComments';

const ToolbarAsync = React.lazy(() => import('./toolbar'));

const GlossTitle: React.SFC<IProps> = (props: IProps) => {
    const {
        gloss,
        toolbar,
        roleManager,
    } = props;

    const className = classNames({ rejected: gloss.isRejected });
    const isAuthenticated = roleManager.currentRole !== SecurityRole.Anonymous;

    return <h3 className="gloss-word">
        <NeologismIndicator gloss={gloss} />
        <span itemProp="headline" className={className}>
            {gloss.word}
        </span>
        {gloss.inflectedWord && <span className="gloss-word__inflection">
            {gloss.inflectedWord.word.toLocaleLowerCase() !== gloss.word.toLocaleLowerCase() &&
                <span className="gloss-word__inflection__word">
                    {gloss.inflectedWord.word.toLocaleLowerCase()}
                </span>}
            <span className="gloss-word__inflection__name">
                {gloss.inflectedWord.speech}
            </span>
            {gloss.inflectedWord.inflections && gloss.inflectedWord.inflections.map(
                (inflection) => <span key={inflection.inflectionId} className="gloss-word__inflection__name">
                    {inflection.name}
                </span>)}
        </span>}
        {toolbar && <Suspense fallback={null}>
            <NumberOfComments gloss={gloss} />
            <GlossAbsoluteLink gloss={gloss} />
            {isAuthenticated && <ToolbarAsync gloss={gloss} />}
        </Suspense>}
    </h3>;
};

GlossTitle.defaultProps = {
    roleManager: resolve(DI.RoleManager),
};

export default GlossTitle;
