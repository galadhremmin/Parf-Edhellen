import { IProps } from './GlossGroupLabel._types';

const GlossGroupLabel = (props: IProps) => {
    const {
        glossGroupLabel,
        label: glossLabel,
        isOld,
    } = props.gloss;

    const label = glossLabel ?? glossGroupLabel;
    if (! label && ! isOld) {
        return null;
    }

    return <span className="gloss-word__neologism position-absolute top-0 start-0 ms-1 translate-middle-y">
        {label && <span className="badge rounded-pill badge-sm bg-info" title={label}>{label}</span>}{' '}
        {!! isOld && <span className="badge rounded-pill badge-sm bg-danger" title="This gloss was imported from a dictionary that hasn't been updated for years.">Old source</span>}
    </span>;
};

GlossGroupLabel.defaultProps = {
    isOld: false,
};

export default GlossGroupLabel;
