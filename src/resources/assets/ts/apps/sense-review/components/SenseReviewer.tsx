import { useCallback, useEffect, useRef, useState } from 'react';

import { resolve } from '@root/di';
import { DI } from '@root/di/keys';
import Spinner from '@root/components/Spinner';

import type { IDecision, IDecisionResponse, ISenseUnderReview } from '@root/connectors/backend/ISenseReviewApi';
import type { IProps, Relation } from './SenseReviewer._types';
import ReferenceLink from '@root/apps/book-browser/components/GlossaryEntities/ReferenceLink';
import CandidateCard from './CandidateCard';
import MeaningSearch from './MeaningSearch';
import RewordSense from './RewordSense';

import './SenseReviewer.scss';

/** How many candidates answer to a number key. */
const ShortcutCandidates = 9;

/**
 * Works through the senses no rule and no judge could place, one at a time: what the sense says, the entries that
 * use it, and the meanings it could have. A decision is written through the editor's own path, so it is locked
 * against every later automated pass.
 */
const SenseReviewer = (props: IProps) => {
    const api = props.api ?? resolve(DI.SenseReviewApi);
    const reasons = props.reasons ?? {};

    const [ sense, setSense ] = useState<ISenseUnderReview | null>(null);
    const [ waiting, setWaiting ] = useState<number>(props.waiting ?? 0);
    const [ byReason, setByReason ] = useState<Record<string, number>>(props.byReason ?? {});
    const [ reason, setReason ] = useState<string>('');
    const [ loading, setLoading ] = useState<boolean>(true);
    const [ saving, setSaving ] = useState<boolean>(false);
    const [ error, setError ] = useState<string | null>(null);
    const [ notice, setNotice ] = useState<{ kind: 'success' | 'warning'; message: string } | null>(null);

    // the senses passed over in this sitting, so the queue steps past them without forgetting them
    const skipped = useRef<number[]>([]);
    const heading = useRef<HTMLHeadingElement>(null);

    const load = useCallback(async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await api.next(skipped.current, reason);
            setSense(response.sense);
            setWaiting(response.waiting);
            setByReason(response.byReason ?? {});
        } catch (_) {
            setError('The next sense could not be loaded. Try again in a moment.');
        } finally {
            setLoading(false);
        }
    }, [ api, reason ]);

    useEffect(() => {
        void load();
    }, [ load ]);

    const decide = useCallback(async (decision: IDecision, describe: string) => {
        if (sense === null || saving) {
            return;
        }

        setSaving(true);
        setError(null);

        let response: IDecisionResponse;
        try {
            response = await api.decide(sense.senseId, {
                ...decision,
                offered: sense.candidates.map((candidate) => candidate.synsetId),
            });
        } catch (_) {
            setError('That decision was not recorded. Try again.');
            setSaving(false);
            return;
        }

        setNotice(response.assigned || decision.dismiss === true
            ? { kind: 'success', message: `“${sense.sense}” — ${describe}. ${response.waiting} left.` }
            // the sense was locked by another editor between loading it and deciding on it
            : { kind: 'warning', message: `“${sense.sense}” was not changed: ${response.outcome}.` });

        // whatever happens next, the decision itself is recorded: say so rather than blame it for a failed reload
        await load();
        setSaving(false);
    }, [ api, load, sense, saving ]);

    const chooseCandidate = useCallback((synsetId: string, relation: Relation) => {
        const candidate = sense?.candidates.find((c) => c.synsetId === synsetId);
        void decide({ synsetId, relation },
            relation === 'kind_of' ? `a kind of ${candidate?.label}` : `means ${candidate?.label}`);
    }, [ decide, sense ]);

    const chooseConcept = useCallback((conceptId: number) => {
        void decide({ conceptId }, 'meaning chosen by hand');
    }, [ decide ]);

    const reword = useCallback(async (wording: string) => {
        if (sense === null || saving) {
            return;
        }

        setSaving(true);
        setError(null);

        try {
            const response = await api.reword(sense.senseId, wording);
            const moved = response.result.entries;

            setNotice({
                kind: 'success',
                message: `${moved} ${moved === 1 ? 'entry' : 'entries'} now read “${response.result.sense}”.`
                    + ` ${response.waiting} left.`,
            });
            await load();
        } catch (_) {
            setError('Those entries were not reworded. Try again.');
        } finally {
            setSaving(false);
        }
    }, [ api, load, sense, saving ]);

    const dismiss = useCallback(() => {
        void decide({ dismiss: true }, 'not a meaning');
    }, [ decide ]);

    const skip = useCallback(() => {
        if (sense === null) {
            return;
        }

        skipped.current = [ ...skipped.current, sense.senseId ];
        void load();
    }, [ load, sense ]);

    /**
     * Keeps the new sense's name in sight without hauling the page about: `nearest` scrolls only when the heading
     * has gone off screen, and then no further than it must. The sticky bar carries the wording the rest of the time.
     */
    useEffect(() => {
        // jsdom has no scrollIntoView, and a page that will not scroll is no reason to fail a decision
        heading.current?.scrollIntoView?.({ block: 'nearest' });
    }, [ sense?.senseId ]);

    // a queue of a thousand is worked by keyboard: a number picks a meaning, x dismisses, n moves on. Not "s":
    // the glossary above binds that site-wide to focus its search box, which would swallow the keys that follow.
    useEffect(() => {
        const onKeyDown = (ev: KeyboardEvent) => {
            const target = ev.target as HTMLElement;
            if (ev.metaKey || ev.ctrlKey || ev.altKey || saving || sense === null
                || /^(INPUT|TEXTAREA|SELECT)$/.test(target?.tagName ?? '')) {
                return;
            }

            const ordinal = Number.parseInt(ev.key, 10);
            if (ordinal >= 1 && ordinal <= ShortcutCandidates) {
                const candidate = sense.candidates[ordinal - 1];
                if (candidate) {
                    ev.preventDefault();
                    chooseCandidate(candidate.synsetId, sense.viaPhraseHead ? 'kind_of' : 'synonym');
                }
                return;
            }

            if (ev.key === 'x') {
                ev.preventDefault();
                dismiss();
            } else if (ev.key === 'n') {
                ev.preventDefault();
                skip();
            }
        };

        window.addEventListener('keydown', onKeyDown);
        return () => window.removeEventListener('keydown', onKeyDown);
    }, [ chooseCandidate, dismiss, saving, sense, skip ]);

    return <div className="SenseReviewer">
        <header className="SenseReviewer--header">
            <div>
                {sense !== null && <span className="SenseReviewer--header-sense">{sense.sense}</span>}
                <strong>{waiting.toLocaleString()}</strong> waiting
                {skipped.current.length > 0 && <span className="text-muted">
                    {' '}· {skipped.current.length} skipped in this sitting
                </span>}
            </div>
            <div>
                <label className="form-label mb-0 me-2" htmlFor="sense-review-reason">Show</label>
                <select className="form-select form-select-sm d-inline-block w-auto" id="sense-review-reason"
                    value={reason} disabled={saving} onChange={(ev) => setReason(ev.target.value)}>
                    <option value="">every reason</option>
                    {Object.keys(reasons).map((key) => <option key={key} value={key}>
                        {key.replace(/_/g, ' ')}{byReason[key] ? ` (${byReason[key]})` : ''}
                    </option>)}
                </select>
            </div>
        </header>

        <details className="SenseReviewer--guide">
            <summary>How this works</summary>
            <p>
                A sense is the English that entries are glossed with. Give it the meaning it has, and the dictionary
                can answer <em>kinds of tree</em> as well as <em>tree</em>.
            </p>
            <p>
                Take <strong>Means this</strong> when the card is the sense itself, and <strong>A kind of this</strong>
                when the sense is narrower than the card: mallorn is a kind of tree, not another word for one. If no
                card fits, search the taxonomy for a plainer word.
            </p>
            <p>
                Not every sense is a meaning. A name, a grammatical label or an Elvish word left untranslated belongs
                to <strong>Not a meaning</strong>. If the entries don’t tell you enough, skip it and leave it for
                someone who knows the language.
            </p>
            <p className="mb-0">
                Your choice is locked: no rule or model will overrule it afterwards. Press a number to take that
                card’s meaning, <kbd>x</kbd> to dismiss, <kbd>n</kbd> for the next one.
            </p>
        </details>

        {notice !== null && <div className={`alert alert-${notice.kind === 'success' ? 'success' : 'warning'}`}
            role="status">{notice.message}</div>}
        {error !== null && <div className="alert alert-danger" role="alert">{error}</div>}

        {loading && <p className="text-muted"><Spinner /> Finding the next sense…</p>}

        {! loading && sense === null && <p>
            <em>Nothing left to review{reason === '' ? '' : ' for that reason'}.</em>
        </p>}

        {! loading && sense !== null && <article className="SenseReviewer--sense" aria-busy={saving}
            key={sense.senseId}>
            <h2 className="SenseReviewer--wording" ref={heading} tabIndex={-1}>{sense.sense}</h2>

            <p className="SenseReviewer--facts">
                <span className="badge text-bg-secondary">{sense.entries} {sense.entries === 1 ? 'entry' : 'entries'}</span>
                {sense.speeches.map((speech) => <span key={speech} className="badge text-bg-light">{speech}</span>)}
                <span className="badge text-bg-warning">{sense.reason.replace(/_/g, ' ')}</span>
                {sense.confidence !== null && <span className="badge text-bg-light">
                    confidence {sense.confidence}
                </span>}
            </p>

            <p className="text-muted">
                {reasons[sense.reason] ?? ''}
                {sense.detail !== null && <span> It answered: <code>{sense.detail}</code>.</span>}
            </p>

            {sense.usages.length > 0 && <section className="SenseReviewer--usages">
                <h3 className="h6">Glossed with it</h3>
                <ul>
                    {sense.usages.map((usage) => <li key={usage.lexicalEntryId}>
                        {/* loads the entry into the glossary above, in place: no reload, no rewritten URL */}
                        <ReferenceLink lexicalEntryId={usage.lexicalEntryId} url={usage.url}>
                            {usage.word}
                        </ReferenceLink>
                        <span className="text-muted">
                            {' '}{usage.language}{usage.speech === null ? '' : `, ${usage.speech}`}
                        </span>
                        {usage.glosses !== '' && <span>: {usage.glosses}</span>}
                    </li>)}
                </ul>
                {sense.entries > sense.usages.length && <p className="small text-muted mb-0">
                    and {sense.entries - sense.usages.length} more
                </p>}
            </section>}

            <section className="SenseReviewer--candidates">
                <h3 className="h6">
                    What does it mean?
                    {sense.viaPhraseHead && <small className="text-muted fw-normal">
                        {' '}The meanings below belong to “{sense.headword}”, the head of the phrase — so this sense is
                        most likely <em>a kind of</em> one of them.
                    </small>}
                </h3>

                {sense.candidates.length === 0
                    ? <p className="text-muted">
                        The taxonomy knows no meaning for “{sense.headword}”. Search for one below, or say it is no
                        meaning at all.
                    </p>
                    : <div className="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                        {sense.candidates.map((candidate, index) => <div className="col" key={candidate.synsetId}>
                            <CandidateCard candidate={candidate} ordinal={index + 1} disabled={saving}
                                viaPhraseHead={sense.viaPhraseHead} onChoose={chooseCandidate} />
                        </div>)}
                    </div>}
            </section>

            <MeaningSearch headword={sense.headword} disabled={saving} senseApi={props.senseApi}
                onChoose={chooseConcept} />

            <RewordSense entries={sense.entries} wording={sense.sense} disabled={saving} senseApi={props.senseApi}
                onReword={(wording) => void reword(wording)} />

            <footer className="SenseReviewer--actions">
                <button type="button" className="btn btn-outline-danger" disabled={saving} onClick={dismiss}>
                    Not a meaning <kbd className="SenseReviewer--shortcut">x</kbd>
                </button>
                <button type="button" className="btn btn-outline-secondary" disabled={saving} onClick={skip}>
                    Skip <kbd className="SenseReviewer--shortcut">n</kbd>
                </button>
                {saving && <span className="text-muted"><Spinner /> Recording…</span>}
            </footer>

        </article>}
    </div>;
};

export default SenseReviewer;
