import { useCallback, useMemo, useState } from 'react';

import Ad from '@root/apps/ad';
import type { ILexicalEntryEntity } from '@root/connectors/backend/IBookApi';
import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import Language from '../Language';
import LexicalEntry from './LexicalEntry';
import type { IProps } from './GlossaryLanguage._types';

/**
 * Picks the entry that deserves the featured (full-width) treatment: the first
 * entry that is current, not rejected and not from an outdated source. The backend
 * already sorts entries by relevance rating, so the first qualifying entry is the
 * best match. Sections with a single entry render full-width anyway, so featuring
 * only applies when there is something to contrast against.
 */
export function findFeaturedEntry(entries: ILexicalEntryEntity[]): ILexicalEntryEntity | null {
    if (entries.length < 2) {
        return null;
    }
    return entries.find((e) => e.isLatest && ! e.isRejected && ! e.isOld) ?? null;
}

export default function GlossaryLanguage(props: IProps) {
    const {
        entries,
        featured = false,
        language,
        word,
        onReferenceLinkClick,
    } = props;

    // A signed-in user can pin a different entry into the featured slot when they
    // disagree with the algorithm's pick (e.g. searching "house" surfaces "car(dh)"
    // but they want "adab"). This is a view-only override for the current session —
    // it doesn't persist as a standing preference — plus a fire-and-forget analytics
    // signal so ranking accuracy can be reviewed later.
    const [overrideId, setOverrideId] = useState<number | null>(null);

    const algorithmicFeaturedEntry = useMemo(
        () => (featured ? findFeaturedEntry(entries) : null), [featured, entries]);

    const featuredEntry = overrideId != null
        ? entries.find((e) => e.id === overrideId) ?? algorithmicFeaturedEntry
        : algorithmicFeaturedEntry;

    const _onPromoteFeatured = useCallback((entry: ILexicalEntryEntity) => {
        const previousLexicalEntryId = featuredEntry?.id;
        setOverrideId(entry.id);

        if (entry.id === previousLexicalEntryId) {
            return;
        }

        const api = resolve(DI.BookApi);
        api.promoteFeaturedEntry({
            languageId: language.id,
            lexicalEntryId: entry.id,
            previousLexicalEntryId,
            searchWord: word,
        }).catch((err) => console.warn(err));
    }, [featuredEntry, language.id, word]);

    const currentEntries = entries.filter((e) => ! e.isOld && e !== featuredEntry);
    const oldEntries = entries.filter((e) => !! e.isOld && e !== featuredEntry);

    return <article className="ed-glossary__language" id={`glossary-lang-${language.id}`}>
        <header>
            <Language language={language} />
        </header>
        {featuredEntry && <section className="ed-glossary__language__featured">
            <LexicalEntry lexicalEntry={featuredEntry} featured={true}
                toolbar={true} onReferenceLinkClick={onReferenceLinkClick} />
        </section>}
        {currentEntries.length > 0 && <section className="ed-glossary__language__words">
            {currentEntries.map((entry) => <LexicalEntry lexicalEntry={entry} key={entry.id}
                toolbar={true} onReferenceLinkClick={onReferenceLinkClick}
                onPromoteFeatured={featured ? () => _onPromoteFeatured(entry) : undefined} />)}
        </section>}
        {oldEntries.length > 0 && <>
            <div className="ed-glossary__language__outdated-label" role="presentation">Older sources</div>
            <section className="ed-glossary__language__words ed-glossary__language__words--outdated">
                {oldEntries.map((entry) => <LexicalEntry lexicalEntry={entry} key={entry.id} demoted={true}
                    toolbar={true} onReferenceLinkClick={onReferenceLinkClick} />)}
            </section>
        </>}
        <section className="mt-3">
            <Ad ad="glossary" />
        </section>
    </article>;
}
