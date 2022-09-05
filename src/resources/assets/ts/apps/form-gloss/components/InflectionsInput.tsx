import {
    CellValueChangedEvent,
    ColDef,
    ColGroupDef,
    GetRowIdParams,
    GridReadyEvent,
} from '@ag-grid-community/core';
import { ClientSideRowModelModule } from '@ag-grid-community/client-side-row-model';
import { AgGridReact } from '@ag-grid-community/react';
import InflectionCellEditor from '@root/components/Grid/cell-editors/InflectionCellEditor';
import { IFragmentGridMetadata } from '@root/components/Grid/cell-editors/InflectionCellEditor._types';
import SpeechSelectCellEditor from '@root/components/Grid/cell-editors/SpeechSelectCellEditor';
import BooleanCellEditor from '@root/components/Grid/cell-editors/BooleanCellEditor';
import BooleanRenderer from '@root/components/Grid/renderers/BooleanRenderer';
import LockedRenderer from '@root/components/Grid/renderers/LockedRenderer';
import InflectionRenderer from '@root/components/Grid/renderers/InflectionRenderer';
import SpeechRenderer from '@root/components/Grid/renderers/SpeechRenderer';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';
import { DI, resolve } from '@root/di';
import React, { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { IProps } from './InflectionsInput._types';
import { IInflectionGroupState } from '../reducers/InflectionsReducer._types';
import { fireEventAsync } from '@root/components/Component';

import '@ag-grid-community/core/dist/styles/ag-grid.css';
import '@ag-grid-community/core/dist/styles/ag-theme-balham.css';
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
        Promise.all([
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

            const columnDefinition = [
                {
                    colId: 'inflection-word',
                    editable: true,
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
                },
                {
                    cellEditor: BooleanCellEditor,
                    cellRenderer: BooleanRenderer,
                    editable: true,
                    headerName: 'Rejected?',
                    field: 'is_rejected',
                    resizable: true,
                },
                {
                    cellEditor: BooleanCellEditor,
                    cellRenderer: BooleanRenderer,
                    editable: true,
                    headerName: 'Neologism?',
                    field: 'is_neologism',
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

        const resizeGrid = () => {
            if (gridRef.current) {
                gridRef.current.api.sizeColumnsToFit();
            }
        };

        window.addEventListener('resize', resizeGrid);
        return () => {
            window.removeEventListener('resize', resizeGrid);
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
    }, [inflections]);

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

        fireEventAsync('InflectionsInput', onChange, {
            inflection,
            rowId: node.id,
        });
    };

    return <div className="ag-theme-balham InflectionsInput--container">
        {gridColumnDefinition &&
            <AgGridReact
                modules={[ClientSideRowModelModule]}
                getRowId={_onGridGetRowId}
                // rowClassRules={RowClassRules}
                columnDefs={gridColumnDefinition}
                // defaultColDef={DefaultColumnDefinition}
                enableBrowserTooltips={true}
                rowData={inflections}
                onCellValueChanged={_onGridCellValueChanged}
                onGridReady={_onGridReady}
                ref={gridRef}
            />}
    </div>;
}

InflectionsInput.defaultProps = {
    inflectionApi: resolve(DI.InflectionApi),
    speechApi: resolve(DI.SpeechApi),
};

export default InflectionsInput;
