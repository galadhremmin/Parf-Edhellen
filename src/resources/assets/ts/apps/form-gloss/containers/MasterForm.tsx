import React, { useState } from 'react';
import { connect } from 'react-redux';

import { ReduxThunkDispatch } from '@root/_types';
import { fireEvent } from '@root/components/Component';
import ValidationErrorAlert from '@root/components/Form/ValidationErrorAlert';
import Quote from '@root/components/Quote';
import StaticAlert from '@root/components/StaticAlert';
import TextIcon from '@root/components/TextIcon';
import { deepClone } from '@root/utilities/func/clone';
import { isEmptyString } from '@root/utilities/func/string-manipulation';
import { IInflectionGroupState } from '../reducers/InflectionsReducer._types';

import GlossActions from '../actions/GlossActions';
import GlossForm from '../components/GlossForm';
import InflectionForm from '../components/InflectionForm';
import { FormSection } from '../index._types';
import { RootReducer } from '../reducers';
import { IChangeTrackerReducerState } from '../reducers/ChangeTrackerReducer._types';
import { IProps } from './MasterForm._types';

function MasterForm(props: IProps) {
    const {
        changes,
        confirmButton,
        edit,
        errors,
        formSections,
        gloss,
        inflections,

        onCopyGloss,
        onGlossFieldChange,
        onInflectionCreate,
        onInflectionsChange,
        onSubmit,
    } = props;

    const [ showNoChangesWereMade, setShowNoChangesWereMade ] = useState<boolean>(false);

    const _onDisableEdit = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        void fireEvent('MasterForm', onCopyGloss);
    };

    const _onSubmit = (ev: React.FormEvent) => {
        ev.preventDefault();
        setShowNoChangesWereMade(! Object.keys(changes) //
            .some((context: keyof IChangeTrackerReducerState) => changes[context] === true)
        );

        void fireEvent('MasterForm', onSubmit, {
            changes,
            edit,
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
        {showNoChangesWereMade && <StaticAlert type="info">
            <TextIcon icon="info-sign" />{' '}
            <strong>No changes were made!</strong> Please make at least one change before trying to submit.
        </StaticAlert>}
        {formSections.includes(FormSection.Gloss) && <section>
            <GlossForm name="ed-gloss-form"
                    gloss={gloss}
                    onGlossFieldChange={onGlossFieldChange} 
            />
        </section>}
        {formSections.includes(FormSection.Inflections) && <section className="mt-3">
            <InflectionForm name="ed-inflections-form"
                            inflections={inflections}
                            glossId={gloss.id}
                            onInflectionCreate={onInflectionCreate}
                            onInflectionsChange={onInflectionsChange}
            />
        </section>}
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

const mapDispatchToProps = (dispatch: ReduxThunkDispatch) => {
    const actions = new GlossActions();
    return {
        onCopyGloss: () => {
            dispatch(actions.setEditingGlossId(0));
        },
        onGlossFieldChange: ({value: v}) => {
            dispatch(actions.setGlossField(v.field, v.value));
        },
        onInflectionCreate: () => {
            dispatch(actions.createInflectionGroup());
        },
        onInflectionsChange: ({value: v}) => {
            dispatch(actions.setInflectionGroup(v.inflectionGroupUuid, v.inflectionGroup));
        },
        onSubmit: ({value: v}) => {
            const {
                changes,
                edit,
                gloss: originalGloss,
                inflections: originalInflections,
            } = v;

            const gloss = deepClone(originalGloss);
            const inflections = originalInflections.filter((i: IInflectionGroupState) => ! isEmptyString(i.word));

            if (changes.glossChanged) {
                // grandfathered sanitization logic from the GlossForm.
                if (! edit) {
                    delete gloss.id;
                }
        
                if (gloss.tengwar.length < 1) {
                    delete gloss.tengwar;
                }

                void dispatch(actions.saveGloss(gloss, changes.inflectionsChanged //
                    ? inflections : null));
            } else if (changes.inflectionsChanged) {
                void dispatch(actions.saveInflections( //
                    inflections, //
                    gloss.contributionId,
                    gloss.id));
            } else {
                // Neither inflections nor gloss is modified - do nothing!
                // TODO: Better error handling? Notify the user that nothing's done?
            }
        },
    } as IProps;
};

export default connect<IProps, IProps, IProps>(mapStateToProps, mapDispatchToProps)(MasterForm);
