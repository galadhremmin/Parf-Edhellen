import React from 'react';
import axios from 'axios';
import EDConfig from 'ed-config';

class EDComments extends React.Component {
    constructor(props) {
        super(props);

        this.state = {

        };
    }

    load(offset, parentPostId) {
        if (offset === undefined) {
            offset = 0;
        }

        
    }
}

EDComments.defaultProps = {
    context: ''
}

export default EDComments;