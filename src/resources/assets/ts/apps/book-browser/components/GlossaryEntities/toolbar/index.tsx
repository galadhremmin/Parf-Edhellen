import DeleteLexicalEntry from './DeleteLexicalEntry';
import EditLexicalEntry from './EditLexicalEntry';
import PromoteFeaturedEntry from './PromoteFeaturedEntry';
import SaveToWordList from './SaveToWordList';

import type { IProps } from './index._types';

function Toolbar(props: IProps) {
    return <>
        <PromoteFeaturedEntry {...props} />
        <SaveToWordList {...props} />
        <DeleteLexicalEntry {...props} />
        <EditLexicalEntry {...props}  />
    </>;
}

export default Toolbar;
