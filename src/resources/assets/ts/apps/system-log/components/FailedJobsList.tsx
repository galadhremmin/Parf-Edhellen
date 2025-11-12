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

import { IFailedJob } from '@root/connectors/backend/ILogApi';
import { IProps } from './LogList._types';

import '@root/components/AgGrid.scss';
import '@root/utilities/agGridModules';

const ColumnDefinitions: ColDef<IFailedJob>[] = [
    {
        field: 'failedAt',
        valueFormatter: params => DateTime.fromISO(params.value).toLocaleString(DateTime.DATETIME_SHORT_WITH_SECONDS),
    },
    {
        field: 'queue',
    },
    { field: 'uuid' },
    {
        cellEditor: 'agLargeTextCellEditor',
        cellEditorPopup: true,
        editable: true,
        field: 'exception',
        minWidth: 300,
    },
    {
        cellEditor: 'agLargeTextCellEditor',
        cellEditorPopup: true,
        editable: true,
        field: 'payload',
        minWidth: 300,
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

function FailedJobsList({ logApi }: IProps) {
    const onGridReady = useCallback((params: GridReadyEvent) => {
        const dataSource: IDatasource = {
            rowCount: undefined,
            getRows: (params) => {
                logApi.getFailedJobs(params.startRow, params.endRow)
                    .then((data) => {
                        let lastRow = -1;
                        if (data.length <= params.endRow) {
                            lastRow = data.length;
                        }
                        params.successCallback(data.errors, lastRow);
                    })
                    .catch((e) => {
                        console.error(`FailedJobsList values promise: ${e}`);
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

export default FailedJobsList;
