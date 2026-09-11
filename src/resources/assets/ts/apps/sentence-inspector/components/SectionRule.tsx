/**
 * A section break. The corpus encodes these as a run of hyphens in a fragment of its own
 * (the Túrin wrapper has two), so they are drawn rather than typed, and they take no line
 * number: the numbering counts lines of text, not decoration.
 */
export default function SectionRule() {
    return <div className="phrase-line phrase-line--rule">
        <div className="ed-rule" aria-hidden="true">&#10022;</div>
    </div>;
}
