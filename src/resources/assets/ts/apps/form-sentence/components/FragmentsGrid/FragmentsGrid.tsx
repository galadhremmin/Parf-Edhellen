import React from 'react';

import { AgGridReact } from '@ag-grid-community/react';
import {
    AllCommunityModules,
    GridReadyEvent,
} from '@ag-grid-community/all-modules';
import '@ag-grid-community/all-modules/dist/styles/ag-grid.css';
import '@ag-grid-community/all-modules/dist/styles/ag-theme-balham.css';

import {
    DI,
    resolve,
} from '@root/di';
import SpeechResourceApiConnector from '@root/connectors/backend/SpeechResourceApiConnector';

import {
    FragmentGridColumns,
    RelevantFragmentTypes,
} from './config';
import {
    IProps,
    IState,
} from './FragmentsGrid._types';

import './FragmentsGrid.scss';

class FragmentsGrid extends React.Component<IProps, IState> {
    public state: IState = {
        gridParameters: null,
    };

    public async componentDidMount() {
        const speeches = await resolve<SpeechResourceApiConnector>(DI.SpeechApi).speeches();
        this.setState({
            gridParameters: {
                cellRendererParams: {
                    speeches,
                },
            },
        });
    }

    public render() {
        const {
            gridParameters,
        } = this.state;

        const fragments = this.getRelevantFragments();

        return <div className="ag-theme-balham FragmentsGrid--container">
            {gridParameters &&
                <AgGridReact modules={AllCommunityModules}
                    columnDefs={FragmentGridColumns}
                    rowData={fragments}
                    onGridReady={this._onGridReady}
                    defaultColDef={gridParameters}
                />}
        </div>;
    }

    private getRelevantFragments() {
        const {
            fragments,
        } = this.props;

        return fragments.filter((f) => RelevantFragmentTypes.includes(f.type));
    }

    private _onGridReady = (params: GridReadyEvent) => {
        // this.gridApi = params.api;

        // this.gridColumnApi = params.columnApi;

        params.api.sizeColumnsToFit();
    };
}

export default FragmentsGrid;
