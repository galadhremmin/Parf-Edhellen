import React from 'react';

import { IProps } from './Markdown._types';

export default class Markdown extends React.PureComponent<IProps> {
    public static getDerivedStateFromProps(props: IProps) {
        if (props.parse) {
            
        }
    }

    public render() {
        return <span />;
    }
}
