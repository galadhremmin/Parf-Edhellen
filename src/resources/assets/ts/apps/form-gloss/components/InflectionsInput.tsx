import type {
    CellValueChangedEvent,
    ColDef,
    ColGroupDef,
    EditableCallbackParams,
    GetRowIdParams,
    GridReadyEvent,
    ICellRendererParams,
} from '@ag-grid-community/core';
import { AgGridReact } from '@ag-grid-community/react';
import { fireEventAsync } from '@root/components/Component';
import BooleanCellEditor from '@root/components/Grid/cell-editors/BooleanCellEditor';
import InflectionCellEditor from '@root/components/Grid/cell-editors/InflectionCellEditor';
import type { IFragmentGridMetadata } from '@root/components/Grid/cell-editors/InflectionCellEditor._types';
import SpeechSelectCellEditor from '@root/components/Grid/cell-editors/SpeechSelectCellEditor';
import BooleanRenderer from '@root/components/Grid/renderers/BooleanRenderer';
import InflectionRenderer from '@root/components/Grid/renderers/InflectionRenderer';
import LockedRenderer from '@root/components/Grid/renderers/LockedRenderer';
import SpeechRenderer from '@root/components/Grid/renderers/SpeechRenderer';
import type { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import type { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';
import { useEffect, useLayoutEffect, useMemo, useRef, useState } from 'react';
import { deepClone } from '@root/utilities/func/clone';
import '@root/utilities/agGridModules';
import type { IInflectionGroupState } from '../reducers/InflectionsReducer._types';
import type { IProps } from './InflectionsInput._types';

import '@ag-grid-community/styles/ag-grid.css';
import '@ag-grid-community/styles/ag-theme-balham.css';
import './InflectionsInput.scss';

function InflectionsInput(props: IProps) {
    const [ gridColumnDefinition, setColumnDefinition ] = useState<(ColDef | ColGroupDef)[]>(null);
    const gridRef = useRef<AgGridReact>(null);

    const {
        inflections,
        inflectionApi,
        focusNextRow,
        speechApi,

        onChange,
    } = props;

    useEffect(() => {
        void Promise.all([
            inflectionApi.inflections(),
            speechApi.speeches()
        ]).then(([ allInflections, allSpeeches ]) => {
            const inflections = new Map<number, IInflection>();
            const groupedInflections = new Map<string, IInflection[]>();
            const speeches = new Map<number, ISpeechEntity>();

            for (const inflectionCategory of Object.entries(allInflections)) {
                groupedInflections.set(inflectionCategory[0], inflectionCategory[1]);
                for (const inflection of inflectionCategory[1]) {
                    inflections.set(inflection.id, inflection);
                }
            }

            for (const speech of allSpeeches) {
                speeches.set(speech.id, speech);
            }

            const cellRendererParams: IFragmentGridMetadata = {
                groupedInflections,
                inflections,
                speeches,
            };

            const columnDefinition: ColDef[] = [
                {
                    cellRenderer: (params: ICellRendererParams) => {
                        if ((params.data as IInflectionGroupState).sentenceFragmentId) {
                            return <LockedRenderer {...params} />;
                        }

                        return params.value as string;
                    },
                    colId: 'inflection-word',
                    editable: (params: EditableCallbackParams) => //
                        ! (params.data as IInflectionGroupState).sentenceFragmentId,
                    headerName: 'Word',
                    field: 'word',
                    resizable: true,
                },
                {
                    cellEditor: SpeechSelectCellEditor,
                    cellEditorParams: cellRendererParams,
                    cellEditorPopup: true,
                    cellRenderer: SpeechRenderer,
                    cellRendererParams,
                    editable: true,
                    headerName: 'Speech',
                    field: 'speechId',
                    resizable: true,
                },
                {
                    cellEditor: InflectionCellEditor,
                    cellEditorParams: cellRendererParams,
                    cellEditorPopup: true,
                    cellRenderer: InflectionRenderer,
                    cellRendererParams,
                    editable: true,
                    headerName: 'Inflections',
                    field: 'inflections',
                    resizable: true,
                    valueParser: (value) => value,
                },
                {
                    cellEditor: BooleanCellEditor,
                    cellRenderer: BooleanRenderer,
                    editable: true,
                    headerName: 'Rejected?',
                    field: 'isRejected',
                    resizable: true,
                },
                {
                    cellEditor: BooleanCellEditor,
                    cellRenderer: BooleanRenderer,
                    editable: true,
                    headerName: 'Neologism?',
                    field: 'isNeologism',
                    resizable: true,
                },
                {
                    headerName: 'Source',
                    editable: true,
                    field: 'source',
                    resizable: true,
                },
                {
                    cellRenderer: LockedRenderer,
                    editable: false,
                    headerName: 'In phrase',
                    field: 'sentence.name',
                    resizable: true,
                },
            ];

            setColumnDefinition(columnDefinition);
        });

        const __onResizeGrid = () => {
            if (gridRef.current) {
                gridRef.current.api.sizeColumnsToFit();
            }
        };

        window.addEventListener('resize', __onResizeGrid);
        return () => {
            window.removeEventListener('resize', __onResizeGrid);
        };
    }, []);

    useLayoutEffect(() => {
        if (focusNextRow && gridRef.current) {
            const api = gridRef.current.api;
            requestIdleCallback(() => {
                const rowIndex = inflections.length - 1;
                api.setFocusedCell(rowIndex, 'inflection-word');
                api.startEditingCell({
                    rowIndex,
                    colKey: 'inflection-word',
                });
            });
        }
    }, [inflections, focusNextRow]);

    // Create a deep clone of the inflection data to ensure we're not passing
    // references to mutated state objects. This is required because the grid
    // is not aware of the immutability of the state.
    const rowData = useMemo(() => 
        inflections.map(inflection => deepClone(inflection)), 
        [inflections]
    );

    const _onGridGetRowId = (row: GetRowIdParams) => {
        return (row.data as IInflectionGroupState).inflectionGroupUuid;
    }

    const _onGridReady = (e: GridReadyEvent) => {
        e.api.sizeColumnsToFit();
    };

    const _onGridCellValueChanged = (e: CellValueChangedEvent) => {
        const {
            data: inflection,
            node,
        } = e;

        void fireEventAsync('InflectionsInput', onChange, {
            inflection,
            rowId: node.id,
        });
    };

    return <div className="ag-theme-balham InflectionsInput--container">
        {gridColumnDefinition &&
            <AgGridReact
                // modules={[ClientSideRowModelModule]}
                getRowId={_onGridGetRowId}
                // rowClassRules={RowClassRules}
                columnDefs={gridColumnDefinition}
                // defaultColDef={DefaultColumnDefinition}
                enableBrowserTooltips={true}
                rowData={rowData}
                onCellValueChanged={_onGridCellValueChanged}
                onGridReady={_onGridReady}
                ref={gridRef}
            />}
    </div>;
}

export default withPropInjection(InflectionsInput, {
    inflectionApi: DI.InflectionApi,
    speechApi: DI.SpeechApi,
});
