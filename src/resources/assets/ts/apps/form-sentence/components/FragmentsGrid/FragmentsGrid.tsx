import React from 'react';

import { AgGridReact } from '@ag-grid-community/react';
import {
    AllCommunityModules,
    CellValueChangedEvent,
    DetailGridInfo,
    GridReadyEvent,
    RowNode,
} from '@ag-grid-community/all-modules';
import '@ag-grid-community/all-modules/dist/styles/ag-grid.css';
import '@ag-grid-community/all-modules/dist/styles/ag-theme-balham.css';

import {
    DI,
    resolve,
} from '@root/di';
import { fireEvent } from '@root/components/Component';
import IGlossResourceApi, { IGlossEntity } from '@root/connectors/backend/IGlossResourceApi';
import {
    IInflection,
    IInflectionResourceApi,
} from '@root/connectors/backend/IInflectionResourceApi';
import ISpeechResourceApi, { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import GlossCellEditor from './cell-editors/GlossCellEditor';
import InflectionCellEditor from './cell-editors/InflectionCellEditor';
import SpeechSelectCellEditor from './cell-editors/SpeechSelectCellEditor';
import GlossRenderer from './renderers/GlossRenderer';
import InflectionRenderer from './renderers/InflectionRenderer';
import SpeechRenderer from './renderers/SpeechRenderer';
import TengwarRenderer from './renderers/TengwarRenderer';
import {
    FragmentGridColumnDefinition,
    IProps,
    IState,
} from './FragmentsGrid._types';

import './FragmentsGrid.scss';
import { ISentenceFragmentEntity, SentenceFragmentType } from '@root/connectors/backend/IBookApi';

class FragmentsGrid extends React.Component<IProps, IState> {
    public state: IState = {
        columnDefinition: null,
        inflections: null,
        groupedInflections: null,
        speeches: null,
    };

    private _glossCache: Map<number, Promise<IGlossEntity>>;
    private _gridRef: AgGridReact;
    private _glossApi: IGlossResourceApi;

    constructor(props: IProps) {
        super(props);
        this._glossCache = new Map();
        this._gridRef = null;
        this._glossApi = resolve<IGlossResourceApi>(DI.GlossApi);
    }

    public async componentDidMount() {
        const [ groupedInflections, speeches ] = await Promise.all([
            resolve<IInflectionResourceApi>(DI.InflectionApi).inflections(),
            resolve<ISpeechResourceApi>(DI.SpeechApi).speeches(),
        ]);

        const groupedInflectionsMap = new Map<string, IInflection[]>();
        const inflectionMap = new Map<number, IInflection>();
        const speechMap = new Map<number, ISpeechEntity>();

        Object.keys(groupedInflections) //
            .forEach((group) => {
                for (const inflection of groupedInflections[group]) {
                    inflectionMap.set(inflection.id, inflection);
                }

                groupedInflectionsMap.set(group, groupedInflections[group]);
            });

        for (const speech of speeches) {
            speechMap.set(speech.id, speech);
        }

        const cellRendererParams = {
            groupedInflections: groupedInflectionsMap,
            inflections: inflectionMap,
            resolveGloss: this._onResolveGloss,
            speeches: speechMap,
            suggestGloss: this._onSuggestGloss,
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
                cellEditor: GlossCellEditor,
                cellEditorParams: cellRendererParams,
                cellRenderer: GlossRenderer,
                cellRendererParams,
                editable: true,
                field: 'glossId',
            },
            {
                cellEditor: SpeechSelectCellEditor,
                cellEditorParams: cellRendererParams,
                cellRenderer: SpeechRenderer,
                cellRendererParams,
                editable: true,
                field: 'speechId',
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
            fragments,
        } = this.props;

        return <>
            <div className="ag-theme-balham FragmentsGrid--container">
                {columnDefinition &&
                    <AgGridReact columnDefs={columnDefinition}
                        isExternalFilterPresent={this._onIsExternalFilterPresent}
                        doesExternalFilterPass={this._onDoesExternalFilterPass}
                        modules={AllCommunityModules}
                        onCellValueChanged={this._onCellValueChanged}
                        onGridReady={this._onGridReady}
                        ref={this._onSetGridReference}
                        rowData={fragments}
                    />}
            </div>
            <p>
                <strong>Tip!</strong> While searching for glosses, repeat vowels for longer sounds
                (eg. <em>niin</em> matches <em>nín</em> and <em>niiin</em> matches <em>nîn</em>).
            </p>
        </>;
    }

    private _onWindowResize = () => {
        const {
            _gridRef: gridRef,
        } = this;

        if (gridRef) {
            (gridRef as any as DetailGridInfo).api.sizeColumnsToFit();
        }
    }

    private _onIsExternalFilterPresent = () => true;

    private _onDoesExternalFilterPass = (ev: RowNode) => {
        const data = ev.data as ISentenceFragmentEntity;
        return data.type === SentenceFragmentType.Word;
    };

    private _onCellValueChanged = (ev: CellValueChangedEvent) => {
        let {
            newValue: value,
        } = ev;

        const {
            column,
            data: fragment,
        } = ev;

        const {
            onChange,
        } = this.props;

        // always trim strings from unnecessary whitespace!
        if (typeof value === 'string') {
            value = value.trim();
        }

        const field = column.getColId();
        fireEvent(this, onChange, {
            field,
            fragment,
            value,
        });
    }

    private _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit()
    }

    private _onSetGridReference = (gridRef: AgGridReact) => {
        this._gridRef = gridRef;
    }

    private _onResolveGloss = (glossId: number) => {
        if (this._glossCache.has(glossId)) {
            return this._glossCache.get(glossId);
        }

        const glossPromise = this._glossApi.gloss(glossId);
        this._glossCache.set(glossId, glossPromise);
        return glossPromise;
    }

    private _onSuggestGloss = async (word: string) => {
        const {
            languageId,
        } = this.props;

        const suggestions = await this._glossApi.suggest({
            inexact: true,
            languageId,
            parameterized: true,
            words: [word],
        });

        if (suggestions.size > 0) {
            return suggestions.values().next().value;
        }

        return null;
    };
}

export default FragmentsGrid;
