import React from 'react';
import { IProps } from '../containers/Toolbar._types';
import ActionLink from './ActionLink';

export default class DeletePost extends React.PureComponent<IProps> {
    public render() {
        return <ActionLink icon="trash" {...this.props}>
            Delete
        </ActionLink>;
    }
}
