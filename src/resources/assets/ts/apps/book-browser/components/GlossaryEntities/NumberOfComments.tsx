import TextIcon from '@root/components/TextIcon';
import { IProps } from './NumberOfComments._types';

const NumberOfComments = (props: IProps) => {
    const { lexicalEntry } = props;

    return <a href={`/wt/${lexicalEntry.id}`} className="ed-comments-no"
        title="See all versions and read comments">
        <TextIcon icon="comment" />
        {' '}
        <span className="no">{lexicalEntry.commentCount}</span>
    </a>;
};

export default NumberOfComments;
