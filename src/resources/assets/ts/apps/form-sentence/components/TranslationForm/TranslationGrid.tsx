import React, { useEffect, useRef } from 'react';
import {
    CellValueChangedEvent,
    GridReadyEvent,
} from 'ag-grid-community';
import { AgGridReact } from 'ag-grid-react/lib/agGridReact';
// import { ClientSideRowModelModule } from 'ag-grid-community/client-side-row-model';
import { fireEventAsync } from '@root/components/Component';
import {
    IProps,
    TranslationGridColumnDefinition,
} from './TranslationGrid._types';

const columnDefinition: TranslationGridColumnDefinition = [
    {
        cellEditorPopup: true,
        editable: false,
        field: 'sentenceText',
        resizable: true,
    },
    {
        cellEditorPopup: true,
        editable: true,
        field: 'translation',
        cellEditor: 'agLargeTextCellEditor',
        resizable: true,
    },
];

export default function TranslationGrid(props: IProps) {
    const gridRef = useRef<AgGridReact>(null);

    const {
        onChange,
        rows,
    } = props;

    useEffect(() => {
        const _onWindowResize = () => {    
            if (gridRef) {
                gridRef.current?.api.sizeColumnsToFit();
            }
        }

        window.addEventListener('resize', _onWindowResize);
        return () => {
            window.removeEventListener('resize', _onWindowResize);
        };
    }, []);

    const _onCellValueChanged = (ev: CellValueChangedEvent) => {
        const {
            data: row,
            newValue: value,
        } = ev;

        fireEventAsync('TranslationGrid', onChange, {
            ...row,
            translation: String(value).trim(),
        });
    }

    const _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit();
    }

    const _onSetGridReference = (ref: AgGridReact) => {
        gridRef.current = ref;
    }

    return <div className="ag-theme-balham FragmentsGrid--container">
        {columnDefinition &&
            <AgGridReact
//                modules={[ClientSideRowModelModule]}
                columnDefs={columnDefinition}
                onCellValueChanged={_onCellValueChanged}
                onGridReady={_onGridReady}
                ref={_onSetGridReference}
                rowData={rows}
            />}
    </div>;
}
