import type {
    CellValueChangedEvent,
    GridReadyEvent,
} from '@ag-grid-community/core';
import { useEffect, useRef } from 'react';
import { fireEventAsync } from '@root/components/Component';
import { useAgGridThemeClass } from '@root/utilities/useAgGridThemeClass';
import { isEmptyString } from '@root/utilities/func/string-manipulation';
import { AgGridReact } from '@ag-grid-community/react';
import '@root/utilities/agGridModules';
import type {
    IProps,
    TranslationGridColumnDefinition,
} from './TranslationGrid._types';

const columnDefinition: TranslationGridColumnDefinition = [
    {
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
    {
        editable: true,
        field: 'sentenceNumber',
        resizable: true,
        type: 'numericColumn',
        width: 50,
    },
    {
        editable: true,
        field: 'paragraphNumber',
        resizable: true,
        type: 'numericColumn',
        width: 50,
    },
];

export default function TranslationGrid(props: IProps) {
    const agGridThemeClass = useAgGridThemeClass();
    const gridRef = useRef<AgGridReact>(null);

    const {
        onChange,
        rows,
    } = props;

    useEffect(() => {
        const __onWindowResize = () => {    
            if (gridRef) {
                gridRef.current?.api.sizeColumnsToFit();
            }
        }

        window.addEventListener('resize', __onWindowResize);
        return () => {
            window.removeEventListener('resize', __onWindowResize);
        };
    }, []);

    const _onCellValueChanged = (ev: CellValueChangedEvent) => {
        const {
            data: row,
            newValue: value,
        } = ev;

        void fireEventAsync('TranslationGrid', onChange, {
            ...row,
            translation: isEmptyString(value) ? '' : String(value).trim(),
        });
    }

    const _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit();
    }

    const _onSetGridReference = (ref: AgGridReact) => {
        gridRef.current = ref;
    }

    return <div className={`${agGridThemeClass} FragmentsGrid--container`}>
        {columnDefinition &&
            <AgGridReact
                columnDefs={columnDefinition}
                onCellValueChanged={_onCellValueChanged}
                onGridReady={_onGridReady}
                ref={_onSetGridReference}
                rowData={rows}
            />}
    </div>;
}
