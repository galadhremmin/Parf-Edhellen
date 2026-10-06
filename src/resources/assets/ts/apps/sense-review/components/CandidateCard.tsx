import type { ICandidateProps } from './SenseReviewer._types';

/**
 * One meaning on offer: what it is called, what it means, the other words it goes by and what it is a kind of.
 *
 * The primary button says what the sense most likely is. For a phrase looked up by its head word — "mouth of a
 * river" under "mouth" — that is *a kind of* the candidate; otherwise it is the candidate itself.
 */
const CandidateCard = (props: ICandidateProps) => {
    const { candidate, disabled, onChoose, ordinal, viaPhraseHead } = props;
    const primary: 'synonym' | 'kind_of' = viaPhraseHead ? 'kind_of' : 'synonym';
    const secondary: 'synonym' | 'kind_of' = viaPhraseHead ? 'synonym' : 'kind_of';

    const label = (relation: 'synonym' | 'kind_of') => relation === 'synonym' ? 'Means this' : 'A kind of this';

    return <div className="SenseReviewer--candidate card h-100">
        <div className="card-body">
            <h3 className="SenseReviewer--candidate-title h6">
                {ordinal < 10 && <kbd className="SenseReviewer--shortcut">{ordinal}</kbd>}
                {' '}{candidate.label}{' '}
                <small className="text-muted fw-normal">{candidate.pos}</small>
            </h3>
            <p className="SenseReviewer--definition mb-2">{candidate.definition}</p>
            {candidate.synonyms.length > 0 && <p className="small text-muted mb-1">
                Also: {candidate.synonyms.join(', ')}
            </p>}
            {candidate.lineage.length > 0 && <p className="small text-muted mb-0">
                A kind of {candidate.lineage.join(' › ')}
            </p>}
        </div>
        <div className="card-footer bg-transparent border-0 pt-0 d-flex gap-2 flex-wrap">
            <button type="button" className="btn btn-sm btn-primary" disabled={disabled}
                onClick={() => onChoose(candidate.synsetId, primary)}>
                {label(primary)}
            </button>
            <button type="button" className="btn btn-sm btn-outline-secondary" disabled={disabled}
                onClick={() => onChoose(candidate.synsetId, secondary)}>
                {label(secondary)}
            </button>
        </div>
    </div>;
};

export default CandidateCard;
