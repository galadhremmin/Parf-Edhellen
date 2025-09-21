import {
    CellValueChangedEvent,
    ColDef,
    DetailGridInfo,
    GetRowIdParams,
    GridReadyEvent,
    RowClassParams,
    RowNode,
} from 'ag-grid-community';
import { AgGridReact } from 'ag-grid-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
// import { ClientSideRowModelModule } from 'ag-grid-community/client-side-row-model';

import { fireEventAsync } from '@root/components/Component';
import { ISentenceFragmentEntity, SentenceFragmentType } from '@root/connectors/backend/IBookApi';
import { ILexicalEntryEntity, ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import {
    IInflection
} from '@root/connectors/backend/IInflectionResourceApi';
import { ISpeechEntity } from '@root/connectors/backend/ISpeechResourceApi';

import GlossCellEditor from '@root/components/Grid/cell-editors/GlossCellEditor';
import InflectionCellEditor from '@root/components/Grid/cell-editors/InflectionCellEditor';
import SpeechSelectCellEditor from '@root/components/Grid/cell-editors/SpeechSelectCellEditor';
import GlossRenderer from '@root/components/Grid/renderers/GlossRenderer';
import InflectionRenderer from '@root/components/Grid/renderers/InflectionRenderer';
import SpeechRenderer from '@root/components/Grid/renderers/SpeechRenderer';
import TengwarRenderer from '@root/components/Grid/renderers/TengwarRenderer';
import { withPropInjection } from '@root/di';
import { ISentenceFragmentReducerState } from '../../reducers/child-reducers/SentenceFragmentReducer._types';
import {
    FragmentGridColumnDefinition,
    IProps,
    IState,
} from './FragmentsGrid._types';

import { IAugmentedCellRendererParams } from '@root/components/Grid/cell-editors/InflectionCellEditor._types';
import { DI } from '@root/di/keys';
import './FragmentsGrid.scss';

const DefaultColumnDefinition = {
    tooltipField: '_error',
} as ColDef;

const RowClassRules = {
    'bg-warning': (params: RowClassParams) => {
        const {
            _error: error,
        } = params.data as ISentenceFragmentReducerState;
        return Array.isArray(error);
    },
};

export function FragmentsGrid(props: IProps) {
    const gridRef = useRef<AgGridReact & DetailGridInfo>();
    const glossCacheRef = useRef<Map<number, Promise<ILexicalEntryEntity>>>();

    const [ columnDefinition, setColumnDefinition ] = useState<IState['columnDefinition']>(null);
    const [ inflections, setInflections ] = useState<IState['inflections']>(null);
    const [ groupedInflections, setGroupedInflections ] = useState<IState['groupedInflections']>(null);
    const [ speeches, setSpeeches ] = useState<IState['speeches']>(null);

    const {
        fragments,
        languageId,
        onChange,

        inflectionApi,
        glossApi,
        speechApi,
    } = props;

    useEffect(() => {
        glossCacheRef.current = new Map();

        void Promise.all([ inflectionApi.inflections(), speechApi.speeches(), ]).then(([groupedInflections, speeches]) => {
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
                resolveGloss: _onResolveGloss,
                speeches: speechMap,
                suggestGloss: _onSuggestGloss,
            } as IAugmentedCellRendererParams;
    
            const nextColumnDefinition: FragmentGridColumnDefinition = [
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
                    cellEditorPopup: true,
                    cellRenderer: GlossRenderer,
                    cellRendererParams,
                    editable: true,
                    headerName: 'Lexical entry',
                    field: 'lexicalEntryId',
                    resizable: true,
                    valueFormatter: (params) => {
                        // AG Grid needs a value formatter for object data
                        // The actual display is handled by GlossRenderer
                        return params.value ? 'Lexical Entry' : '';
                    },
                    valueParser: (params) => {
                        // AG Grid needs a value parser for object data
                        // The actual editing is handled by GlossCellEditor
                        return params.newValue;
                    },
                },
                {
                    cellEditor: SpeechSelectCellEditor,
                    cellEditorParams: cellRendererParams,
                    cellEditorPopup: true,
                    cellRenderer: SpeechRenderer,
                    cellRendererParams,
                    editable: true,
                    headerName: 'Speech',
                    field: 'speechId',
                    resizable: true,
                    valueFormatter: (params) => {
                        // AG Grid needs a value formatter for object data
                        // The actual display is handled by SpeechRenderer
                        return params.value ? 'Speech' : '';
                    },
                    valueParser: (params) => {
                        // AG Grid needs a value parser for object data
                        // The actual editing is handled by SpeechSelectCellEditor
                        return params.newValue;
                    },
                },
                {
                    cellEditor: InflectionCellEditor,
                    cellEditorParams: cellRendererParams,
                    cellEditorPopup: true,
                    cellRenderer: InflectionRenderer,
                    cellRendererParams,
                    editable: true,
                    headerName: 'Inflections',
                    field: 'lexicalEntryInflections',
                    resizable: true,
                    valueFormatter: (params) => {
                        // AG Grid needs a value formatter for object data
                        // The actual display is handled by InflectionRenderer
                        return params.value ? 'Inflections' : '';
                    },
                    valueParser: (params) => {
                        // AG Grid needs a value parser for object data
                        // The actual editing is handled by InflectionCellEditor
                        return params.newValue;
                    },
                },
                {
                    cellEditorPopup: true,
                    editable: true,
                    field: 'comments',
                    cellEditor: 'agLargeTextCellEditor',
                    resizable: true,
                },
            ];

            setColumnDefinition(nextColumnDefinition);
            setInflections(inflectionMap);
            setGroupedInflections(groupedInflectionsMap);
            setSpeeches(speechMap);
        });

        /**
         * Resizes the grid's columns appropriately when the viewport changes.
         */
        const __onWindowResize = () => {
            gridRef.current?.api.sizeColumnsToFit();
        }

        window.addEventListener('resize', __onWindowResize);
        return () => {
            window.removeEventListener('resize', __onWindowResize);
        };
    }, []);


    /**
     * Triggers the component's `onChange` event for the specified fragment, informing underlying data model to
     * assign the specified field to the given value. This will not trigger a re-render of the component (unless
     * the field is `fragment`) so this can lead to view model/data model discrepancies.
     * @param fragment fragment to update (make sure it has the `id` property not null and/or 0)
     * @param field field to update
     * @param value value to assign
     */
    const _notifyChange = <T extends keyof ISentenceFragmentEntity>(fragment: ISentenceFragmentEntity, field: T,
        value: ISentenceFragmentEntity[T]) => {
        void fireEventAsync('FragmentsGrid', onChange, {
            field,
            fragment,
            value,
        });
    }

    /**
     * Updates agGrid's local state *and* the underlying data model by setting the same gloss and speech
     * to fragments that are similar to the one specified.
     * @param fragment fragment that was changed.
     * @param lexicalEntryId gloss ID assigned to the specified fragment.
     */
    const _useGlossToUpdateSimilarFragments = useCallback(async (fragment: ISentenceFragmentEntity, lexicalEntryId: number) => {
        if (! lexicalEntryId || ! gridRef.current?.api) {
            return;
        }

        const api = gridRef.current?.api;
        const gloss = await _onResolveGloss(lexicalEntryId);

        const transaction: ISentenceFragmentEntity[] = [];
        api.forEachNodeAfterFilter((node) => {
            const f = node.data as ISentenceFragmentEntity;

            if (f.type !== SentenceFragmentType.Word ||
                f.fragment.toLocaleLowerCase() !== fragment.fragment.toLocaleLowerCase()) {
                return;
            }

            let changed = false;
            if (! f.lexicalEntryId || f === fragment) {
                f.lexicalEntryId = lexicalEntryId;
                changed = true;
                _notifyChange(f, 'lexicalEntryId', f.lexicalEntryId);
            }

            if (! f.speechId || f === fragment) {
                f.speechId = gloss.speechId || null;
                changed = true;
                _notifyChange(f, 'speechId', f.speechId);
            }

            if (changed) {
                transaction.push(f);
            }
        });

        if (transaction.length > 0) {
            api.applyTransaction({
                update: transaction,
            });
        }
    }, [ gridRef ]);

    /**
     * Tells agGrid that there a custom filter function is exists.
     */
    const _onIsExternalFilterPresent = () => true;

    /**
     * Custom agGrid view filter that only shows words.
     */
    const _onDoesExternalFilterPass = (ev: RowNode) => {
        const data = ev.data as ISentenceFragmentEntity;
        return data.type === SentenceFragmentType.Word;
    }

    /**
     * On-change handler for agGrid.
     */
    const _onCellValueChanged = (ev: CellValueChangedEvent) => {
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
        if (field === 'lexicalEntryId') {
            void _useGlossToUpdateSimilarFragments(fragment, value);
        } else {
            _notifyChange(fragment, field, value);
        }
    }

    /**
     * Returns an unique string identifier given a fragment entity. This is required
     * by agGrid's `immutableData`.
     */
    const _onGetRowId = (row: GetRowIdParams) => {
        return (row.data as ISentenceFragmentEntity).id.toString(10);
    }

    /**
     * Grid initialization (once it mounts).
     */
    const _onGridReady = (params: GridReadyEvent) => {
        params.api.sizeColumnsToFit()
    }

    /**
     * Reference callback (assigns `_gridRef`).
     */
    const _onSetGridReference = (ref: AgGridReact) => {
        gridRef.current = ref as any; // this really works!
    }

    /**
     * Attempts to resolve the specified gloss ID using local cache,
     * or with the API. This is an asychronous operation.
     * @param lexicalEntryId gloss ID to look up
     * @returns A gloss resolver promise.
     */
    const _onResolveGloss = useCallback((lexicalEntryId: number): Promise<ILexicalEntryEntity> => {
        if (glossCacheRef.current?.has(lexicalEntryId)) {
            return glossCacheRef.current.get(lexicalEntryId);
        }

        if (! lexicalEntryId) {
            return Promise.resolve<ILexicalEntryEntity>(null);
        }

        const glossPromise = glossApi.lexicalEntry(lexicalEntryId);
        glossCacheRef.current?.set(lexicalEntryId, glossPromise);
        return glossPromise;
    }, [glossApi]);

    /**
     * Retrieves suggestions asynchronously for the specified word.
     * @param word look-up word
     */
    const _onSuggestGloss = useCallback(async (word: string): Promise<ISuggestionEntity[]> => {
        const suggestions = await glossApi.suggest({
            inexact: true,
            languageId,
            parameterized: true,
            words: [word],
        });

        if (suggestions.size > 0) {
            return suggestions.values().next().value as ISuggestionEntity[];
        }

        return null;
    }, [glossApi, languageId]);

    return <>
        <div className="ag-theme-balham FragmentsGrid--container">
            {columnDefinition &&
                <AgGridReact
                    getRowId={_onGetRowId}
                    isExternalFilterPresent={_onIsExternalFilterPresent}
                    doesExternalFilterPass={_onDoesExternalFilterPass}
                    rowClassRules={RowClassRules}
                    columnDefs={columnDefinition}
                    defaultColDef={DefaultColumnDefinition}
                    enableBrowserTooltips={true}
                    rowData={fragments}
                    onCellValueChanged={_onCellValueChanged}
                    onGridReady={_onGridReady}
                    ref={_onSetGridReference}
                />}
        </div>
        <p>
            <strong>Tip!</strong> While searching for glosses, repeat vowels for longer sounds
            (eg. <em>niin</em> matches <em>nín</em> and <em>niiin</em> matches <em>nîn</em>).
        </p>
    </>;
}

export default withPropInjection(FragmentsGrid, {
    inflectionApi: DI.InflectionApi,
    glossApi: DI.GlossApi,
    speechApi: DI.SpeechApi,
});
