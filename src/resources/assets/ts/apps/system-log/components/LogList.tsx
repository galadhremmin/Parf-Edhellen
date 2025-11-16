import {
    useCallback
} from 'react';

import type {
    ColDef,
    GridReadyEvent,
    IDatasource,
} from '@ag-grid-community/core';
import { AgGridReact } from '@ag-grid-community/react';
import { DateTime } from 'luxon';

import { IErrorEntity } from '@root/connectors/backend/ILogApi';
import { IProps } from './LogList._types';

import '@root/components/AgGrid.scss';
import '@root/utilities/agGridModules';

const ColumnDefinitions: ColDef<IErrorEntity>[] = [
    {
        field: 'createdAt',
        valueFormatter: params => DateTime.fromISO(params.value).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS),
        minWidth: 160,
        filter: 'agDateColumnFilter',
    },
    {
        cellEditor: 'agLargeTextCellEditor',
        cellEditorPopup: true,
        editable: true,
        field: 'message',
        filter: true,
        minWidth: 300,
    },
    {
        cellEditor: 'agLargeTextCellEditor',
        cellEditorPopup: true,
        editable: true,
        field: 'error',
        minWidth: 300,
    },
    {
        editable: true,
        field: 'url',
        minWidth: 200,
        filter: true,
    },
    {
        editable: true,
        field: 'accountId',
        filter: 'agNumberColumnFilter',
    },
    {
        editable: false,
        field: 'duration',
        filter: 'agNumberColumnFilter',
    },
    {
        editable: true,
        field: 'sessionId',
        filter: true,
    },
    {
        editable: true,
        field: 'ip',
        filter: true,
    },
    {
        editable: true,
        field: 'file',
        filter: true,
        resizable: true,
    },
    { field: 'line' },
    { 
        field: 'userAgent',
        filter: true,
        editable: true,
        resizable: true,
    },
    {
        field: 'category',
        filter: true,
    },
];

const DefaultColumnDefinitions = {
    flex: 1,
    minWidth: 100,
    sortable: false,
};

const GridStyle = {
    height: '500px',
};

function LogList({ logApi }: IProps) {
    const onGridReady = useCallback((params: GridReadyEvent) => {
        const dataSource: IDatasource = {
            rowCount: undefined,
            getRows: (params) => {
                logApi.getErrors(params.startRow, params.endRow)
                    .then((data) => {
                        let lastRow = -1;
                        if (data.length <= params.endRow) {
                            lastRow = data.length;
                        }
                        params.successCallback(data.errors, lastRow);
                    })
                    .catch((e) => {
                        console.error(`LogList values promise: ${e}`);
                    });
            },
        };
        params.api.setGridOption('datasource', dataSource);
    }, []);

    return  <div className="ag-theme-balham" style={GridStyle}>
        <AgGridReact
            columnDefs={ColumnDefinitions}
            defaultColDef={DefaultColumnDefinitions}
            rowBuffer={0}
            rowModelType="infinite"
            cacheBlockSize={25}
            cacheOverflowSize={2}
            maxConcurrentDatasourceRequests={1}
            infiniteInitialRowCount={100}
            maxBlocksInCache={10}
            onGridReady={onGridReady}
        />
    </div>;
}

export default LogList;
