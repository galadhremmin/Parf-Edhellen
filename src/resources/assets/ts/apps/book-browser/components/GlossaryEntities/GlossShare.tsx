import TextIcon from '@root/components/TextIcon';
import { IProps } from './GlossShare._types';

const GlossShare = (props: IProps) => {
    const { gloss } = props;

    return <a href={`/wt/${gloss.id}`} className="gloss-link">
        <TextIcon icon="share" />
    </a>;
};

export default GlossShare;
