import React from 'react';

import { AgGridReact } from '@ag-grid-community/react';
import {
    AllCommunityModules,
    GridReadyEvent,
    ValueFormatterParams,
} from '@ag-grid-community/all-modules';
import '@ag-grid-community/all-modules/dist/styles/ag-grid.css';
import '@ag-grid-community/all-modules/dist/styles/ag-theme-balham.css';

import {
    DI,
    resolve,
} from '@root/di';
import {
    IInflection,
    IInflectionResourceApi,
} from '@root/connectors/backend/IInflectionResourceApi';
import ISpeechResourceApi, { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import {
    RelevantFragmentTypes,
} from './config';
import InflectionCellEditor from './cell-editors/InflectionCellEditor';
import InflectionRenderer from './renderers/InflectionRenderer';
import SpeechRenderer from './renderers/SpeechRenderer';
import TengwarRenderer from './renderers/TengwarRenderer';
import {
    FragmentGridColumnDefinition,
    IProps,
    IState,
} from './FragmentsGrid._types';

import './FragmentsGrid.scss';

class FragmentsGrid extends React.Component<IProps, IState> {
    public state: IState = {
        columnDefinition: null,
        inflections: null,
        groupedInflections: null,
        speeches: null,
    };

    public async componentDidMount() {
        const [ groupedInflections, speeches ] = await Promise.all([
            resolve<IInflectionResourceApi>(DI.InflectionApi).inflections(),
            resolve<ISpeechResourceApi>(DI.SpeechApi).speeches(),
        ]);

        const speechMap = new Map<number, ISpeechEntity>();
        const inflectionMap = new Map<number, IInflection>();

        for (const speech of speeches) {
            speechMap.set(speech.id, speech);
        }

        Object.keys(groupedInflections) //
            .forEach((group) => {
                for (const inflection of groupedInflections[group]) {
                    inflectionMap.set(inflection.id, inflection);
                }
            });

        const cellRendererParams = {
            groupedInflections,
            inflections: inflectionMap,
            speeches: speechMap,
        } as IState;

        const columnDefinition: FragmentGridColumnDefinition = [
            {
                editable: false,
                field: 'fragment',
            },
            {
                cellRenderer: TengwarRenderer,
                editable: false,
                field: 'tengwar',
            },
            {
                editable: true,
                field: 'glossId',
            },
            {
                cellEditor: 'agSelectCellEditor',
                cellEditorParams: {
                    values: speeches.map((s) => s.id),
                },
                cellRenderer: SpeechRenderer,
                cellRendererParams,
                editable: true,
                field: 'speechId',
                valueFormatter: this._onSpeechValueFormatted,
            },
            {
                cellEditor: InflectionCellEditor,
                cellEditorParams: cellRendererParams,
                cellRenderer: InflectionRenderer,
                cellRendererParams,
                editable: true,
                field: 'inflections',
            },
            {
                editable: true,
                field: 'comments',
                cellEditor: 'agLargeTextCellEditor',
            },
        ];

        this.setState({
            ...cellRendererParams,
            columnDefinition,
        });
    }

    public render() {
        const {
            columnDefinition,
        } = this.state;

        const fragments = this.getRelevantFragments();

        return <div className="ag-theme-balham FragmentsGrid--container">
            {columnDefinition &&
                <AgGridReact modules={AllCommunityModules}
                    columnDefs={columnDefinition}
                    rowData={fragments}
                    onGridReady={this._onGridReady}
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

    private _onSpeechValueFormatted = (params: ValueFormatterParams) => {
        const {
            speeches,
        } = this.state;

        return speeches.has(params.value) ? speeches.get(params.value).name : 'invalid';
    }
}

export default FragmentsGrid;
