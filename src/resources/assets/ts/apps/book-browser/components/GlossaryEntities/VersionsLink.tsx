import TextIcon from '@root/components/TextIcon';
import { IProps } from './VersionsLink._types';

function VersionsLink(props: IProps) {
    const {
        gloss,
    } = props;

    return <a href={`/wt/${gloss.id}/versions`} title="See earlier versions of this gloss">
        <TextIcon icon="clock-history" />
    </a>;
}

export default VersionsLink;
