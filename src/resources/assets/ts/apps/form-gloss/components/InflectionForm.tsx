import React, { useState } from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Panel from '@root/components/Panel';

import { IProps } from './InflectionForm._types';
import InflectionsInput from '../components/InflectionsInput';
import { IChangeEventArgs } from './InflectionsInput._types';
import { fireEvent } from '@root/components/Component';

function InflectionForm(props: IProps) {
    const [ focusNextRow, setFocusNextRow ] = useState(false);

    const {
        inflections,
        name,

        onInflectionCreate,
        onInflectionsChange,
    } = props;

    const _onChange = (ev: IComponentEvent<IChangeEventArgs>) => {
        const {
            inflection: inflectionGroup,
            rowId: inflectionGroupUuid,
        } = ev.value;

        setFocusNextRow(false);
        fireEvent(name, onInflectionsChange, {
            inflectionGroup,
            inflectionGroupUuid,
        });
    };

    const _onCreateInflectionGroup = () => {
        setFocusNextRow(true);
        fireEvent(name, onInflectionCreate);
    };

    return <div className="row">
        <div className="col-12">
            <Panel title="Inflections" titleButton={
                <button className="btn btn-sm btn-secondary"
                        type="button"
                        onClick={_onCreateInflectionGroup}>Create an inflection</button>}>
                {inflections.length > 0 && <InflectionsInput
                    inflections={inflections}
                    focusNextRow={focusNextRow}
                    onChange={_onChange} 
                />}
            </Panel>
        </div>
    </div>;
}

InflectionForm.defaultProps = {
    inflections: [],
    glossId: null,
    name: 'InflectionForm',
} as IProps;

export default InflectionForm;
