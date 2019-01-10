import React from 'react';
import { IProps } from '../containers/Toolbar._types';
import ActionLink from './ActionLink';

export default class EditPost extends React.PureComponent<IProps> {
    public render() {
        return <ActionLink icon="pencil" {...this.props}>
            Edit
        </ActionLink>;
    }
}
