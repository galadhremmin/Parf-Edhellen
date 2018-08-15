import React from 'react';
import ToolbarButton from './toolbar-button';

const Toolbar = props => [
    <li key="t0">
        <ToolbarButton apiPath={`forum/subscription/${props.threadId}`} 
            apiProp="subscribed" 
            activeLabel="Unsubscribe"
            inactiveLabel="Subscribe"
            glyph="bell"
        />
    </li>
];

export default Toolbar;
