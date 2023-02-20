import DeleteGloss from './DeleteGloss';
import EditGloss from './EditGloss';

import { IProps } from './index._types';

function Toolbar(props: IProps) {
    return <>
        <DeleteGloss {...props} />
        <EditGloss {...props}  />
    </>;
}

export default Toolbar;
