import type { MouseEvent } from 'react';

import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import type { IProps } from './ReferenceLink._types';

/**
 * Links to another resolved lexical entry (a derivation ancestor, a derivative descendant, ...)
 * without a hard page load: firing `loadReference` (the same global event GlossaryEntities'
 * inline reference links and word-finder's GlossList use) lets Orchestrator's listener load the
 * entry in place via Redux (`SearchActions.expandSpecificGloss()`), rather than navigating to
 * `url` and reloading the whole page. `url` is kept as the actual href regardless, so
 * middle-click/open-in-new-tab/right-click still work normally.
 */
const ReferenceLink = (props: IProps) => {
    const { children, className, lexicalEntryId, title, url } = props;

    if (! url) {
        return <span className={className} title={title}>{children}</span>;
    }

    const onClick = (ev: MouseEvent<HTMLAnchorElement>) => {
        if (! lexicalEntryId || ev.button !== 0 || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) {
            return;
        }

        ev.preventDefault();
        const globalEvents = resolve(DI.GlobalEvents);
        globalEvents?.fire(globalEvents.loadReference, { lexicalEntryId });
    };

    return <a className={className} href={url} title={title} onClick={onClick}>{children}</a>;
};

export default ReferenceLink;
