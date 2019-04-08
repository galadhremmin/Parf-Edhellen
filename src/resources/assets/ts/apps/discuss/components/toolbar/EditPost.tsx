import React from 'react';

import ActionLink from './ActionLink';
import { IProps } from './index._types';

export default class EditPost extends React.PureComponent<IProps> {
    public render() {
        return <ActionLink icon="pencil" {...this.props}>
            Edit
        </ActionLink>;
    }
}
