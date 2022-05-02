import {
    CellValueChangedEvent,
    DetailGridInfo,
    GridReadyEvent,
} from '@ag-grid-community/core';
import { AgGridReact } from '@ag-grid-community/react/lib/agGridReact';
import {
    ClientSideRowModelModule,
} from '@ag-grid-community/client-side-row-model';
import React from 'react';

import { fireEventAsync } from '@root/components/Component';
import {
    IProps,
    IState,
    TranslationGridColumnDefinition,
} from './TranslationGrid._types';

export default class TranslationGrid extends React.Component<IProps> {
    public state = {
        columnDefinition: [],
    } as IState;

    private _gridRef: AgGridReact;

    public componentDidMount() {
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

        this.setState({
            columnDefinition,
        });

        window.addEventListener('resize', this._onWindowResize);
    }

    public componentWillUnmount() {
        window.removeEventListener('resize', this._onWindowResize);
    }

    public render() {
        const {
            columnDefinition,
        } = this.state;

        const {
            rows,
        } = this.props;

        return <div className="ag-theme-balham FragmentsGrid--container">
            {columnDefinition &&
                <AgGridReact
                    modules={[ClientSideRowModelModule]}
                    columnDefs={columnDefinition}
                    onCellValueChanged={this._onCellValueChanged}
                    onGridReady={this._onGridReady}
                    ref={this._onSetGridReference}
                    rowData={rows}
                />}
        </div>;
    }

    private _onWindowResize = () => {
        const {
            _gridRef: gridRef,
        } = this;

        if (gridRef) {
            gridRef.api.sizeColumnsToFit();
        }
    }

    private _onCellValueChanged = (ev: CellValueChangedEvent) => {
        const {
            data: row,
            newValue: value,
        } = ev;

        const {
            onChange,
        } = this.props;

        fireEventAsync(this, onChange, {
            ...row,
            translation: String(value).trim(),
        });
    }

    private _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit()
    }

    private _onSetGridReference = (gridRef: AgGridReact) => {
        this._gridRef = gridRef;
    }
}
