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
import InflectionRenderer from '@root/components/Grid/renderers/InflectionRenderer';
import SpeechRenderer from '@root/components/Grid/renderers/SpeechRenderer';
import { IInflection } from '@root/connectors/backend/IInflectionResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';
import { DI, resolve } from '@root/di';
import React, { useEffect, useRef, useState } from 'react';
import { IProps } from './InflectionsInput._types';
import { IInflectionGroupState } from '../reducers/InflectionsReducer._types';

function InflectionsInput(props: IProps) {
    const [ gridColumnDefinition, setColumnDefinition ] = useState<(ColDef | ColGroupDef)[]>(null);
    const gridRef = useRef(null);

    const {
        inflections,
        inflectionApi,
        speechApi,
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
                    headerName: 'Source',
                    field: 'source',
                    resizable: true,
                },
                {
                    headerName: 'In phrase',
                    field: 'sentence.name',
                    resizable: true,
                    editable: false,
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

    const _onGridGetRowId = (row: GetRowIdParams) => {
        return (row.data as IInflectionGroupState).inflectionGroupUuid;
    }

    const _onGridReady = (e: GridReadyEvent) => {
        e.api.sizeColumnsToFit();
    };

    const _onGridCellValueChanged = (e: CellValueChangedEvent) => {
        const {
            newValue: value,
            column,
            data: inflection,
        } = e;

        console.log([inflection, column, value]);
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
                // onCellValueChanged={_onGridCellValueChanged}
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
