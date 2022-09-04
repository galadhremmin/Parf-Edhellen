import React, { useRef } from 'react';
import { connect } from 'react-redux';

import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';
import Quote from '@root/components/Quote';

import GlossActions from '../actions/GlossActions';
import GlossForm from '../components/GlossForm';
import InflectionForm from '../components/InflectionForm';
import { RootReducer } from '../reducers';
import { IProps } from './MasterForm._types';

import '@ag-grid-community/core/dist/styles/ag-grid.css';
import '@ag-grid-community/core/dist/styles/ag-theme-balham.css';
import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';

function MasterForm(props: IProps) {
    const {
        changes,
        confirmButton,
        edit,
        errors,
        gloss,
        inflections,

        onCopyGloss,
        onGlossFieldChange,
        onInflectionCreate,
        onInflectionsChange,
        onSubmit,
    } = props;

    const _onDisableEdit = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEvent('MasterForm', onCopyGloss);
    };

    const _onSubmit = async (ev: React.FormEvent) => {
        ev.preventDefault();
        fireEvent('MasterForm', onSubmit, {
            changes,
            gloss,
            inflections,
        });
    };

    return <form onSubmit={_onSubmit}>
        <ValidationErrorAlert error={errors} />
        {edit && <StaticAlert type="warning">
            <p>
                <TextIcon icon="info-sign" />{' '}
                <strong>
                    You are proposing changes to the gloss <Quote>{gloss.word.word}</Quote> ({gloss.id}).
                </strong>{' '}
                You can make a <a href="#" onClick={_onDisableEdit}>copy the gloss</a> if you want to use it as
                a template for a new gloss.
            </p>
        </StaticAlert>}
        <section>
            <GlossForm name="ed-gloss-form"
                    gloss={gloss}
                    onGlossFieldChange={onGlossFieldChange} 
            />
        </section>
        <section className="mt-3">
            <InflectionForm name="ed-inflections-form"
                            inflections={inflections}
                            glossId={gloss.id}
                            onInflectionCreate={onInflectionCreate}
                            onInflectionsChange={onInflectionsChange}
            />
        </section>
        <section className="mt-3 text-center">
            <button type="submit" className="btn btn-primary">{confirmButton || 'Confirm and Save'}</button>
        </section>
    </form>;
}

const mapStateToProps = (state: RootReducer) => ({
    changes: state.changes,
    edit: state.gloss && !! state.gloss.id,
    errors: state.errors,
    gloss: state.gloss,
    inflections: state.inflections,
} as IProps);

const actions = new GlossActions();
const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => ({
    onCopyGloss: () => dispatch(actions.setEditingGlossId(0)),
    onGlossFieldChange: ({value: v}) => dispatch(actions.setGlossField(v.field, v.value)),
    onInflectionCreate: () => dispatch(actions.createInflectionGroup()),
    onInflectionsChange: ({value: v}) => dispatch(actions.setInflectionGroup(v.inflectionGroupUuid, v.inflectionGroup)),
    onSubmit: ({value: v}) => {
        const {
            changes,
            gloss,
            inflections,
        } = v;

        if (changes.glossChanged) {
            dispatch(actions.saveGloss(gloss, changes.inflectionsChanged ? inflections : null));
        } else if (changes.inflectionsChanged) {
            dispatch(actions.saveInflections(gloss.id, inflections));
        } else {
            // Neither inflections nor gloss is modified - do nothing!
            // TODO: Better error handling? Notify the user that nothing's done?
        }
    },
} as IProps);

export default connect<IProps, IProps, IProps>(mapStateToProps, mapDispatchToProps)(MasterForm);
