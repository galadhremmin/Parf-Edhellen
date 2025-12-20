import {
    useCallback,
    useEffect,
    useRef,
    useState,
} from 'react';
import type { MouseEvent } from 'react';

import type {
    ColDef,
    GridReadyEvent,
    IDatasource,
    ICellRendererComp,
    ICellRendererParams,
} from '@ag-grid-community/core';
import { AgGridReact } from '@ag-grid-community/react';
import { formatDateTimeShortWithSeconds } from '@root/utilities/DateTime';
import { SecurityRole } from '@root/config';
import type { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import TextIcon from '@root/components/TextIcon';
import { withPropInjection } from '@root/di';
import { DI } from '@root/di/keys';

import type { IErrorEntity } from '@root/connectors/backend/ILogApi';
import type { IProps } from './LogList._types';

import '@root/components/AgGrid.scss';
import '@root/utilities/agGridModules';

const createDeleteButtonRenderer = (onDelete: (id: number) => void): new () => ICellRendererComp => {
    return class DeleteButtonRenderer implements ICellRendererComp {
        private _button: HTMLAnchorElement | null = null;

        public init(params: ICellRendererParams<IErrorEntity>) {
            if (!params.data) {
                return;
            }

            const button = document.createElement('a');
            button.href = '#';
            button.className = 'TextIcon TextIcon--trash';
            button.textContent = '';
            button.onclick = (e: Event) => {
                e.stopPropagation();
                if (params.data) {
                    onDelete(params.data.id);
                }
            };
            this._button = button;
        }

        public getGui() {
            return this._button || document.createElement('div');
        }

        public refresh(params: ICellRendererParams<IErrorEntity>) {
            return false;
        }

        public destroy() {
            if (this._button) {
                this._button.onclick = null;
                this._button = null;
            }
        }
    };
};

const createColumnDefinitions = (isRoot: boolean, onDelete: (id: number) => void): ColDef<IErrorEntity>[] => {
    const columns: ColDef<IErrorEntity>[] = [
        {
            field: 'createdAt',
            valueFormatter: params => formatDateTimeShortWithSeconds(params.value),
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

    if (isRoot) {
        columns.push({
            headerName: 'Actions',
            cellRenderer: createDeleteButtonRenderer(onDelete),
            width: 100,
            pinned: 'right',
        });
    }

    return columns;
};

const DefaultColumnDefinitions = {
    flex: 1,
    minWidth: 100,
    sortable: false,
};

const GridStyle = {
    height: '500px',
};

function LogList({ logApi, category, week, year, weekNumber, roleManager, onCategoryDeleted }: IProps) {
    const gridRef = useRef<AgGridReact>(null);
    const [deleteErrorId, setDeleteErrorId] = useState<number | null>(null);
    const [deleteCategory, setDeleteCategory] = useState<string | null>(null);
    const isRoot = roleManager?.hasRole(SecurityRole.Root) ?? false;

    const refreshGrid = useCallback(() => {
        if (gridRef.current?.api) {
            gridRef.current.api.refreshInfiniteCache();
        }
    }, []);

    const onDeleteError = useCallback((id: number) => {
        setDeleteErrorId(id);
    }, []);

    const onDeleteErrorConfirmed = useCallback(async (ev: IComponentEvent<number>) => {
        const id = ev.value;
        try {
            await logApi.deleteError(id);
            setDeleteErrorId(null);
            refreshGrid();
        } catch (e) {
            console.error('Failed to delete error:', e);
            setDeleteErrorId(null);
        }
    }, [logApi, refreshGrid]);

    const onDeleteCategoryClick = useCallback(() => {
        if (category) {
            setDeleteCategory(category);
        }
    }, [category]);

    const onDeleteCategoryConfirmed = useCallback(async () => {
        if (!category) {
            return;
        }
        try {
            await logApi.deleteErrorsByCategory(category, year, weekNumber);
            setDeleteCategory(null);
            refreshGrid();
            if (onCategoryDeleted) {
                onCategoryDeleted();
            }
        } catch (e) {
            console.error('Failed to delete category:', e);
            setDeleteCategory(null);
        }
    }, [logApi, category, year, weekNumber, refreshGrid, onCategoryDeleted]);

    const createDataSource = useCallback((currentCategory: string | undefined): IDatasource => {
        return {
            rowCount: undefined,
            getRows: (params) => {
                logApi.getErrors(params.startRow, params.endRow, currentCategory)
                    .then((data) => {
                        let lastRow = -1;
                        if (data.errors.length < params.endRow - params.startRow) {
                            lastRow = params.startRow + data.errors.length;
                        }
                        params.successCallback(data.errors, lastRow);
                    })
                    .catch((e) => {
                        console.error(`LogList values promise: ${e}`);
                        params.failCallback();
                    });
            },
        };
    }, [logApi]);

    const onGridReady = useCallback((params: GridReadyEvent) => {
        params.api.setGridOption('datasource', createDataSource(category));
    }, [createDataSource, category]);

    useEffect(() => {
        if (gridRef.current?.api) {
            gridRef.current.api.setGridOption('datasource', createDataSource(category));
            gridRef.current.api.refreshInfiniteCache();
        }
    }, [category, createDataSource]);

    const columnDefs = createColumnDefinitions(isRoot, onDeleteError);

    return <>
        <div className="ag-theme-balham" style={GridStyle}>
            <AgGridReact
                ref={gridRef}
                columnDefs={columnDefs}
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
        </div>
        {isRoot && category && (
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: '10px' }}>
                <button
                    className="btn btn-secondary"
                    onClick={onDeleteCategoryClick}
                    type="button">
                    <TextIcon icon="trash" />
                    {' '}
                    Delete all events in category: {category}
                    {week && ` (week ${week})`}
                </button>
            </div>
        )}
        {isRoot && (
            <>
                <Dialog<number>
                    open={deleteErrorId !== null}
                    onDismiss={() => setDeleteErrorId(null)}
                    onConfirm={onDeleteErrorConfirmed}
                    title="Delete Error"
                    value={deleteErrorId ?? 0}>
                    <p>Are you sure you want to delete this error log entry (ID: {deleteErrorId})?</p>
                </Dialog>
                <Dialog
                    open={deleteCategory !== null}
                    onDismiss={() => setDeleteCategory(null)}
                    onConfirm={onDeleteCategoryConfirmed}
                    title="Delete Category"
                    confirmButtonText="Delete">
                    <p>Are you sure you want to delete all events in category: <strong>{deleteCategory}</strong>?</p>
                    {week && <p>For week: <strong>{week}</strong></p>}
                    <p>This action cannot be undone.</p>
                </Dialog>
            </>
        )}
    </>;
}

export default withPropInjection(LogList, {
    roleManager: DI.RoleManager,
});
