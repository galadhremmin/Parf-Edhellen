import {
    useCallback,
} from 'react';
import { useAgGridThemeClass } from '@root/utilities/useAgGridThemeClass';

import type {
    ColDef,
    GridReadyEvent,
    IDatasource,
} from '@ag-grid-community/core';
import { AgGridReact } from '@ag-grid-community/react';
import { formatDateTimeShortWithSeconds } from '@root/utilities/DateTime';

import type { IFailedJob } from '@root/connectors/backend/ILogApi';
import type { IProps } from './LogList._types';

import '@root/components/AgGrid.scss';
import '@root/utilities/agGridModules';

const ColumnDefinitions: ColDef<IFailedJob>[] = [
    {
        field: 'failedAt',
        valueFormatter: params => formatDateTimeShortWithSeconds(params.value),
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
    const agGridThemeClass = useAgGridThemeClass();
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

    return  <div className={agGridThemeClass} style={GridStyle}>
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
