import React from 'react';

import { IProps } from './FragmentInspector._types';

export default class FragmentInspector extends React.PureComponent<IProps> {
    render() {
        return <aside className="fragment-inspector">
            {this.props.fragmentId}
        </aside>;
    }
}