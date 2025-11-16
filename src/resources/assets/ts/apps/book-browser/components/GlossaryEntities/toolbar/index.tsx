import DeleteLexicalEntry from './DeleteLexicalEntry';
import EditLexicalEntry from './EditLexicalEntry';

import type { IProps } from './index._types';

function Toolbar(props: IProps) {
    return <>
        <DeleteLexicalEntry {...props} />
        <EditLexicalEntry {...props}  />
    </>;
}

export default Toolbar;
