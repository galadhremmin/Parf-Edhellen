import classNames from 'classnames';
import React, { Suspense } from 'react';

import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import { IProps } from './LexicalEntryTitle._types';

import LexicalEntryGroupLabel from './LexicalEntryGroupLabel';
import NumberOfComments from './NumberOfComments';
import ShareLink from './ShareLink';
import VersionsLink from './VersionsLink';

const ToolbarAsync = React.lazy(() => import('./toolbar'));

const LexicalEntryTitle = (props: IProps) => {
    const {
        lexicalEntry,
        toolbar,
        roleManager,
    } = props;

    const className = classNames({ rejected: lexicalEntry.isRejected });
    const hasWarning = ! lexicalEntry.isCanon || !! lexicalEntry.isUncertain;

    return <h3 className="gloss-word">
        {hasWarning && <span className="uncertain" title="Neologism/unattested">
            <TextIcon icon="asterisk" className="fs-5" />
        </span>}
        <span itemProp="headline" className={className}>
            {lexicalEntry.word}
        </span>
        <LexicalEntryGroupLabel lexicalEntry={lexicalEntry} />
        {lexicalEntry._inflectedWord && <span className="gloss-word__inflection">
            {lexicalEntry._inflectedWord.word.toLocaleLowerCase() !== lexicalEntry.word.toLocaleLowerCase() &&
                <span className="gloss-word__inflection__word">
                    {lexicalEntry._inflectedWord.word.toLocaleLowerCase()}
                </span>}
            <span className="gloss-word__inflection__name">
                {lexicalEntry._inflectedWord.speech}
            </span>
            {(lexicalEntry._inflectedWord.inflections || []).map(
                (i) => <span key={i.inflectionId || i.inflection?.id} className="gloss-word__inflection__name">
                    {i.inflection?.name}
                </span>)}
        </span>}
        {toolbar && <div className="gloss-word--toolbar">
            {! roleManager.isAnonymous && <Suspense fallback={null}>
                <ToolbarAsync lexicalEntry={lexicalEntry} />
            </Suspense>}
            <ShareLink lexicalEntry={lexicalEntry} />
            <VersionsLink lexicalEntry={lexicalEntry} />
            <NumberOfComments lexicalEntry={lexicalEntry} />
        </div>}
    </h3>;
};

export default withPropInjection(LexicalEntryTitle, {
    roleManager: DI.RoleManager,
});
