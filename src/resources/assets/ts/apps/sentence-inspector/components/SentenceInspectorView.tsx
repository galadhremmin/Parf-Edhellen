import React from 'react';

import { IProps } from './SentenceInspectorView._types';

export default class SentenceInspectorView extends React.PureComponent<IProps> {
    public render() {
        return <pre>{JSON.stringify(this.props.sentences)}</pre>;
    }
}
