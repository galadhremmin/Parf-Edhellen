import React from 'react';

export default class Discuss extends React.PureComponent<any> {
    public render() {
        return <span>{JSON.stringify(this.props)}</span>;
    }
}
