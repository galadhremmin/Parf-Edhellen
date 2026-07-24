import type { MouseEvent } from 'react';

import TextIcon from '@root/components/TextIcon';
import type { IProps } from './index._types';

/**
 * Lets a signed-in user pin this entry into the featured (best-match) slot for its
 * language section, when they disagree with the algorithm's pick. Only rendered for
 * entries that aren't already featured — see `GlossaryLanguage`, which supplies
 * `onPromoteFeatured` only to non-featured, non-outdated cards.
 */
function PromoteFeaturedEntry(props: IProps) {
    const {
        lexicalEntry,
        onPromoteFeatured,
    } = props;

    if (! onPromoteFeatured) {
        return null;
    }

    const _onClick = (ev: MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        onPromoteFeatured();
    };

    return <a href="#" onClick={_onClick} title={`Feature ${lexicalEntry.word} as the best match`}>
        <TextIcon icon="pushpin" />
    </a>;
}

export default PromoteFeaturedEntry;
