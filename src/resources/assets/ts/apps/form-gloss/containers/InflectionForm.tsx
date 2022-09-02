import React from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import Panel from '@root/components/Panel';

import GlossActions from '../actions/GlossActions';
import { RootReducer } from '../reducers';
import { IProps } from './InflectionForm._types';
import InflectionsInput from '../components/InflectionsInput';

function InflectionForm(props: IProps) {
    const {
        inflections,
    } = props;

    const _onInflectionSubmit = (ev: React.FormEvent) => {
        ev.preventDefault();
    };

    return <form onSubmit={_onInflectionSubmit}>
        <div className="row mt-3">
            <div className="col-12">
                <Panel title="Inflections">
                    <InflectionsInput inflections={inflections} />
                </Panel>
            </div>
        </div>
    </form>;
}

InflectionForm.defaultProps = {
    confirmButton: 'Confirm and Save',
    inflections: [],
    glossId: null,
    name: 'InflectionForm',
} as IProps;

const mapStateToProps = (state: RootReducer) => ({
    inflections: state.inflections,
    glossId: state.gloss.id,
} as IProps);

const actions = new GlossActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({

} as IProps);

export default connect<IProps, IProps, IProps>(mapStateToProps, mapDispatchToProps)(InflectionForm);
