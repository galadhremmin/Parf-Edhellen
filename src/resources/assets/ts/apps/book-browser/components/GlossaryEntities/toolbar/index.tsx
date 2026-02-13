import DeleteLexicalEntry from './DeleteLexicalEntry';
import EditLexicalEntry from './EditLexicalEntry';
import SaveToWordList from './SaveToWordList';

import type { IProps } from './index._types';

function Toolbar(props: IProps) {
    return <>
        <SaveToWordList {...props} />
        <DeleteLexicalEntry {...props} />
        <EditLexicalEntry {...props}  />
    </>;
}

export default Toolbar;
