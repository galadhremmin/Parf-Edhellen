import classNames from 'classnames';
import React, { Suspense } from 'react';

import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import {
  SecurityRole,
} from '@root/security';

import { IProps } from './GlossTitle._types';

import GlossGroupLabel from './GlossGroupLabel';
import NumberOfComments from './NumberOfComments';
import ShareLink from './ShareLink';
import VersionsLink from './VersionsLink';

const ToolbarAsync = React.lazy(() => import('./toolbar'));

const GlossTitle = (props: IProps) => {
    const {
        gloss,
        toolbar,
        roleManager,
    } = props;

    const className = classNames({ rejected: gloss.isRejected });
    const hasWarning = ! gloss.isCanon || !! gloss.isUncertain;

    return <h3 className="gloss-word">
        {hasWarning && <span className="uncertain" title="Neologism/unattested">
            <TextIcon icon="asterisk" className="fs-5" />
        </span>}
        <span itemProp="headline" className={className}>
            {gloss.word}
        </span>
        <GlossGroupLabel gloss={gloss} />
        {gloss._inflectedWord && <span className="gloss-word__inflection">
            {gloss._inflectedWord.word.toLocaleLowerCase() !== gloss.word.toLocaleLowerCase() &&
                <span className="gloss-word__inflection__word">
                    {gloss._inflectedWord.word.toLocaleLowerCase()}
                </span>}
            <span className="gloss-word__inflection__name">
                {gloss._inflectedWord.speech}
            </span>
            {(gloss._inflectedWord.inflections || []).map(
                (i) => <span key={i.inflectionId || i.inflection?.id} className="gloss-word__inflection__name">
                    {i.inflection?.name}
                </span>)}
        </span>}
        {toolbar && <div className="gloss-word--toolbar">
            {! roleManager.isAnonymous && <Suspense fallback={null}>
                <ToolbarAsync gloss={gloss} />
            </Suspense>}
            <ShareLink gloss={gloss} />
            <VersionsLink gloss={gloss} />
            <NumberOfComments gloss={gloss} />
        </div>}
    </h3>;
};

export default withPropInjection(GlossTitle, {
    roleManager: DI.RoleManager,
});
