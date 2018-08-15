import React from 'react';
import ToolbarButton from './toolbar-button';

const Toolbar = props => [
    <li key="t0">
        <ToolbarButton apiPath={`forum/sticky/${props.threadId}`} 
            apiProp="sticky" 
            activeLabel="Unpin from top"
            inactiveLabel="Pin to top"
            glyph="pushpin"
        />
    </li>
];

export default Toolbar;
