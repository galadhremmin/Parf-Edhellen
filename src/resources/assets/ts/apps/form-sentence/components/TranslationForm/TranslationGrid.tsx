import {
    AllCommunityModules,
    CellValueChangedEvent,
    DetailGridInfo,
    GridReadyEvent,
} from '@ag-grid-community/all-modules';
import { AgGridReact } from '@ag-grid-community/react/lib/agGridReact';
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
                editable: false,
                field: 'sentenceText',
                resizable: true,
            },
            {
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
                <AgGridReact columnDefs={columnDefinition}
                    modules={AllCommunityModules}
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
            (gridRef as any as DetailGridInfo).api.sizeColumnsToFit();
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
            translation: value.trim(),
        });
    }

    private _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit()
    }

    private _onSetGridReference = (gridRef: AgGridReact) => {
        this._gridRef = gridRef;
    }
}
