import {
    useCallback
} from 'react';

import {
    ColDef,
    GridReadyEvent,
    IDatasource,
} from 'ag-grid-community';
import { AgGridReact } from 'ag-grid-react';
import { DateTime } from 'luxon';

import { IErrorEntity } from '@root/connectors/backend/ILogApi';
import { IProps } from './LogList._types';

import '@root/components/AgGrid.scss';

const ColumnDefinitions: ColDef<IErrorEntity>[] = [
    {
        field: 'createdAt',
        valueFormatter: params => DateTime.fromISO(params.value).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS),
        minWidth: 160,
    },
    {
        field: 'message',
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
        field: 'url',
        minWidth: 200,
    },
    { field: 'ip' },
    { field: 'sessionId' },
    { field: 'userAgent' },
    { field: 'category' },
    { field: 'file' },
    { field: 'line' },
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
