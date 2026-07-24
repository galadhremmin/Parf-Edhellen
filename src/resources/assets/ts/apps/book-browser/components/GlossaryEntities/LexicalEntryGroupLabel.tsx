import type { IProps } from './LexicalEntryGroupLabel._types';

const LexicalEntryGroupLabel = (props: IProps) => {
    const {
        lexicalEntryGroupLabel,
        label: originalLabel,
        isOld = false,
    } = props.lexicalEntry;
    const {
        featured = false,
    } = props;

    const label = originalLabel ?? lexicalEntryGroupLabel;
    if (! label && ! isOld && ! featured) {
        return null;
    }

    return <span className="gloss-word__neologism position-absolute top-0 start-0 ms-1 translate-middle-y">
        {!! featured && <span className="badge rounded-pill badge-sm bg-info">Best match</span>}
        {!! label && <span className="badge rounded-pill badge-sm bg-info" title={label}>{label}</span>}{' '}
        {!! isOld && <span className="badge rounded-pill badge-sm bg-danger" title="This gloss was imported from a dictionary that hasn't been updated for years.">Old source</span>}{' '}
    </span>;
};

export default LexicalEntryGroupLabel;
