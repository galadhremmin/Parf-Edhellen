import { useState } from 'react';

import type { IComponentEvent } from '@root/components/Component._types';
import Panel from '@root/components/Panel';

import { fireEvent } from '@root/components/Component';
import InflectionsInput from '../components/InflectionsInput';
import type { IProps } from './InflectionForm._types';
import type { IChangeEventArgs } from './InflectionsInput._types';
import { deepClone } from '@root/utilities/func/clone';

function InflectionForm(props: IProps) {
    const [ focusNextRow, setFocusNextRow ] = useState(false);

    const {
        inflections = [],
        name = 'InflectionForm',

        onInflectionCreate,
        onInflectionsChange,
    } = props;

    const _onChange = (ev: IComponentEvent<IChangeEventArgs>) => {
        const {
            inflection: inflectionGroup,
            rowId: inflectionGroupUuid,
        } = ev.value;

        setFocusNextRow(false);
        void fireEvent(name, onInflectionsChange, {
            inflectionGroup: deepClone(inflectionGroup),
            inflectionGroupUuid,
        });
    };

    const _onCreateInflectionGroup = () => {
        setFocusNextRow(true);
        void fireEvent(name, onInflectionCreate);
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

export default InflectionForm;
