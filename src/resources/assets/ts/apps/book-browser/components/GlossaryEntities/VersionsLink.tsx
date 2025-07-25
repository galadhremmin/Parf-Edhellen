import TextIcon from '@root/components/TextIcon';
import { IProps } from './VersionsLink._types';

function VersionsLink(props: IProps) {
    const {
        lexicalEntry: entry,
    } = props;

    return <a href={`/wt/${entry.id}/versions`} title="See earlier versions of this entry">
        <TextIcon icon="clock-history" />
    </a>;
}

export default VersionsLink;
