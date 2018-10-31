import React from 'react';
import { connect } from 'react-redux';

import { IRootReducer } from '../reducers';
import { IProps } from './GlossaryContainer._types';

export class GlossaryContainer extends React.PureComponent<IProps> {
    public render() {
        return <ul>
            {this.props.languages.map((l) => <li key={l.id}>{l.name}</li>)}
        </ul>;
    }
}

const mapStateToProps = (state: IRootReducer) => ({
    ...state.glossary,

    glosses: state.glosses,
    languages: state.languages,
});

export default connect(mapStateToProps)(GlossaryContainer);
