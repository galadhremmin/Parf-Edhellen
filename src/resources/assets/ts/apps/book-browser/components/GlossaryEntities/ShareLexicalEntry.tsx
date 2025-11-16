import TextIcon from '@root/components/TextIcon';
import type { IProps } from './ShareLexicalEntry._types';

const ShareLexicalEntry = (props: IProps) => {
    const { lexicalEntry: entry } = props;

    return <a href={`/wt/${entry.id}`} className="gloss-link">
        <TextIcon icon="share" />
    </a>;
};

export default ShareLexicalEntry;
