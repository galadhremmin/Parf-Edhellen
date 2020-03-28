import React from 'react';

import { AgGridReact } from '@ag-grid-community/react';
import {
    AllCommunityModules,
    CellValueChangedEvent,
    ColDef,
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
import { fireEventAsync } from '@root/components/Component';
import { SentenceFragmentType, ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import IGlossResourceApi, { IGlossEntity, ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import {
    IInflection,
    IInflectionResourceApi,
} from '@root/connectors/backend/IInflectionResourceApi';
import ISpeechResourceApi, { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import { ISentenceFragmentReducerState } from '../../reducers/child-reducers/SentenceFragmentReducer._types';
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

const DefaultColumnDefinition = {
    tooltipField: '_error',
} as ColDef;

class FragmentsGrid extends React.Component<IProps, IState> {
    public state: IState = {
        columnDefinition: null,
        inflections: null,
        groupedInflections: null,
        speeches: null,
    };

    private _glossCache: Map<number, Promise<IGlossEntity>>;
    private _gridRef: AgGridReact & DetailGridInfo;
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
                headerName: 'Word',
                field: 'fragment',
                resizable: true,
            },
            {
                cellRenderer: TengwarRenderer,
                editable: false,
                field: 'tengwar',
                resizable: true,
            },
            {
                cellEditor: GlossCellEditor,
                cellEditorParams: cellRendererParams,
                cellRenderer: GlossRenderer,
                cellRendererParams,
                editable: true,
                headerName: 'Gloss',
                field: 'glossId',
                resizable: true,
            },
            {
                cellEditor: SpeechSelectCellEditor,
                cellEditorParams: cellRendererParams,
                cellRenderer: SpeechRenderer,
                cellRendererParams,
                editable: true,
                headerName: 'Speech',
                field: 'speechId',
                resizable: true,
            },
            {
                cellEditor: InflectionCellEditor,
                cellEditorParams: cellRendererParams,
                cellRenderer: InflectionRenderer,
                cellRendererParams,
                editable: true,
                field: 'inflections',
                resizable: true,
            },
            {
                editable: true,
                field: 'comments',
                cellEditor: 'agLargeTextCellEditor',
                resizable: true,
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
                    <AgGridReact
                        modules={AllCommunityModules}
                        deltaRowDataMode
                        getRowNodeId={this._onGetRowNodeId}
                        isExternalFilterPresent={this._onIsExternalFilterPresent}
                        doesExternalFilterPass={this._onDoesExternalFilterPass}
                        getRowClass={this._onRowClass}
                        columnDefs={columnDefinition}
                        defaultColDef={DefaultColumnDefinition}
                        enableBrowserTooltips={true}
                        rowData={fragments}
                        onCellValueChanged={this._onCellValueChanged}
                        onGridReady={this._onGridReady}
                        ref={this._onSetGridReference}
                    />}
            </div>
            <p>
                <strong>Tip!</strong> While searching for glosses, repeat vowels for longer sounds
                (eg. <em>niin</em> matches <em>nín</em> and <em>niiin</em> matches <em>nîn</em>).
            </p>
        </>;
    }

    /**
     * Triggers the component's `onChange` event for the specified fragment, informing underlying data model to
     * assign the specified field to the given value. This will not trigger a re-render of the component (unless
     * the field is `fragment`) so this can lead to view model/data model discrepancies.
     * @param fragment fragment to update (make sure it has the `id` property not null and/or 0)
     * @param field field to update
     * @param value value to assign
     */
    private _notifyChange<T extends keyof ISentenceFragmentEntity>(fragment: ISentenceFragmentEntity, field: T,
        value: ISentenceFragmentEntity[T]) {
        const {
            onChange,
        } = this.props;

        fireEventAsync(this, onChange, {
            field,
            fragment,
            value,
        });
    }

    /**
     * Updates agGrid's local state *and* the underlying data model by setting the same gloss and speech
     * to fragments that are similar to the one specified.
     * @param fragment fragment that was changed.
     * @param glossId gloss ID assigned to the specified fragment.
     */
    private async _useGlossToUpdateSimilarFragments(fragment: ISentenceFragmentEntity, glossId: number) {
        if (! glossId) {
            return;
        }

        const gloss = await this._onResolveGloss(glossId);
        const {
            api,
        } = this._gridRef;

        const transaction: ISentenceFragmentEntity[] = [];
        api.forEachNodeAfterFilter((node) => {
            const f = node.data as ISentenceFragmentEntity;

            if (f.type !== SentenceFragmentType.Word ||
                f.fragment.toLocaleLowerCase() !== fragment.fragment.toLocaleLowerCase()) {
                return;
            }

            let changed = false;
            if (! f.glossId || f === fragment) {
                f.glossId = glossId;
                changed = true;
                this._notifyChange(f, 'glossId', f.glossId);
            }

            if (! f.speechId || f === fragment) {
                f.speechId = gloss.speechId || null;
                changed = true;
                this._notifyChange(f, 'speechId', f.speechId);
            }

            if (changed) {
                transaction.push(f);
            }
        });

        if (transaction.length > 0) {
            api.updateRowData({
                update: transaction,
            });
        }
    };

    /**
     * Resizes the grid's columns appropriately when the viewport changes.
     */
    private _onWindowResize = () => {
        const {
            _gridRef: gridRef,
        } = this;

        if (gridRef) {
            gridRef.api.sizeColumnsToFit();
        }
    }

    /**
     * Tells agGrid that there a custom filter function is exists.
     */
    private _onIsExternalFilterPresent = () => true;

    /**
     * Custom agGrid view filter that only shows words.
     */
    private _onDoesExternalFilterPass = (ev: RowNode) => {
        const data = ev.data as ISentenceFragmentEntity;
        return data.type === SentenceFragmentType.Word;
    }

    /**
     * On-change handler for agGrid.
     */
    private _onCellValueChanged = (ev: CellValueChangedEvent) => {
        let {
            newValue: value,
        } = ev;

        const {
            column,
            data: fragment,
        } = ev;

        // always trim strings from unnecessary whitespace!
        if (typeof value === 'string') {
            value = value.trim();
        }

        const field = column.getColId() as keyof ISentenceFragmentEntity;
        if (field === 'glossId') {
            this._useGlossToUpdateSimilarFragments(fragment, value);
        } else {
            this._notifyChange(fragment, field, value);
        }
    }

    /**
     * Returns an unique string identifier given a fragment entity. This is required
     * by agGrid's `deltaRowDataMode`.
     */
    private _onGetRowNodeId = (row: ISentenceFragmentEntity) => {
        return row.id.toString(10);
    }

    /**
     * Returns the row highlight class depending on whether there are errors available.
     */
    private _onRowClass = (params: RowNode) => {
        const {
            _error: error,
        } = params.data as ISentenceFragmentReducerState;
        return Array.isArray(error) ? 'in-error' : null;
    }

    /**
     * Grid initialization (once it mounts).
     */
    private _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit()
    }

    /**
     * Reference callback (assigns `_gridRef`).
     */
    private _onSetGridReference = (gridRef: AgGridReact) => {
        this._gridRef = gridRef as any; // this really works!
    }

    /**
     * Attempts to resolve the specified gloss ID using local cache,
     * or with the API. This is an asychronous operation.
     * @param glossId gloss ID to look up
     * @returns A gloss resolver promise.
     */
    private _onResolveGloss = (glossId: number): Promise<IGlossEntity> => {
        if (this._glossCache.has(glossId)) {
            return this._glossCache.get(glossId);
        }

        if (! glossId) {
            return Promise.resolve<IGlossEntity>(null);
        }

        const glossPromise = this._glossApi.gloss(glossId);
        this._glossCache.set(glossId, glossPromise);
        return glossPromise;
    }

    /**
     * Retrieves suggestions asynchronously for the specified word.
     * @param word look-up word
     */
    private _onSuggestGloss = async (word: string): Promise<ISuggestionEntity[]> => {
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
