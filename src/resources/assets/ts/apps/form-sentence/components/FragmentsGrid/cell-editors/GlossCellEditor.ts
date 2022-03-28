import {
    ICellEditorComp,
    ICellEditorParams,
    PopupComponent,
} from '@ag-grid-community/all-modules';

import { ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import debounce from '@root/utilities/func/debounce';
import { IFragmentGridMetadata } from '../FragmentsGrid._types';

import './GlossCellEditor.scss';

export default class GlossCellEditor extends PopupComponent implements ICellEditorComp {
    private static TEMPLATE = `<div role="presentation" class="GlossCellEditor">
        <input class="GlossCellEditor--input" type="text" list="ag-input-available-values" />
        <div class="GlossCellEditor--scroller"><ul class="GlossCellEditor--suggestions"></ul></div>
    </div>`;

    /**
     * Determines whether the text field should be in focus after the cell editor has mounted.
     */
    private focusAfterAttached: boolean;
    private _value: number;
    private _suggestionIndex: number;
    private _suggestions: ISuggestionEntity[];

    private _editorParams: IFragmentGridMetadata;
    private _inputElement: HTMLInputElement;
    private _suggestionsScroller: HTMLDivElement;
    private _suggestionListElement: HTMLUListElement;

    protected get resolveGloss() {
        return this._editorParams.resolveGloss;
    }

    protected get suggestGloss() {
        return this._editorParams.suggestGloss;
    }

    constructor() {
        super(GlossCellEditor.TEMPLATE);
    }

    public init(params: ICellEditorParams): void {
        this._editorParams = params as IFragmentGridMetadata;

        this._inputElement          = this.getGui().querySelector<HTMLInputElement>('.GlossCellEditor--input');
        this._suggestionsScroller   = this.getGui().querySelector<HTMLDivElement>('.GlossCellEditor--scroller');
        this._suggestionListElement = this.getGui().querySelector<HTMLUListElement>('.GlossCellEditor--suggestions');

        let value = params.value;

        if (params.cellStartedEdit) {
            this.focusAfterAttached = true;

            if ('Backspace' === params.eventKey ||
                'Delete'    === params.eventKey) {
                value = 0;

            } else if (params.charPress) {
                this._inputElement.value = params.charPress;
            }

        } else {
            this.focusAfterAttached = false;
        }

        this._value = value;
        this._suggestions = [];
        this._suggestionIndex = 0;

        if (value !== 0 && this._inputElement.value === '') {
            this._editorParams.resolveGloss(value).then((gloss) => {
                this._inputElement.value = gloss.word.word;
                this._applySuggestions([
                    {
                        accountName: gloss.account.nickname,
                        comments: gloss.comments,
                        glossGroupName: gloss.glossGroup?.name,
                        id: gloss.id,
                        normalizedWord: gloss.word.normalizedWord,
                        translation: gloss.translations.map((t) => t.translation).join(', '),
                        type: gloss.speech?.name,
                        source: gloss.source,
                        word: gloss.word.word,
                    },
                ], 1);
            });
        }

        this.addManagedListener(this._inputElement, 'keydown', this._onKeyDown);
    }

    public afterGuiAttached() {
        if (this.focusAfterAttached) {
            this._inputElement.focus();
            this._inputElement.select();
        }
    }

    public focusIn() {
        this._inputElement.focus();
        this._inputElement.select();
    }

    public getValue() {
        return this._value;
    }

    public isPopup() {
        return true;
    }

    private _applySuggestions(suggestions: ISuggestionEntity[], suggestionIndex = 0) {
        const {
            _suggestionListElement: suggestionListElement,
        } = this;

        const html: string[] = [];

        suggestions.forEach((s, i) => {
            let source = s.source;
            if (source?.length > 40) {
                source = source.substr(0, 40)+ '...';
            }
            html.push(
                `<li>
                    <a href="#" class="GlossCellEditor--suggestion" data-gloss-id="${s.id}">
                        <strong>${s.word}</strong> <i>${s.type || ''}</i> “${s.translation}” [${source || 'unknown source'}] ${s.glossGroupName} (${s.id}, #${i + 1})
                    </a>
                </li>`,
            );
        });

        this._suggestions = suggestions;
        this._suggestionIndex = suggestionIndex;

        suggestionListElement.innerHTML = html.join('');
        suggestionListElement.querySelectorAll('a[data-gloss-id]').forEach((a) => {
            this.addManagedListener(a, 'click', this._onSuggestionClick);
        });
    }

    private _applySuggestion() {
        const index = this._suggestionIndex - 1;
        if (index < 0 || index >= this._suggestions.length) {
            return false;
        }

        const suggestion = this._suggestions[index];
        this._value = suggestion.id;
        this._inputElement.value = suggestion.word;
        return true;
    }

    private _onKeyDown = (ev: KeyboardEvent) => {
        const target = ev.target as HTMLInputElement;
        switch (ev.key) {
            case 'Enter':
                if (target.value.length > 0 && ! this._applySuggestion()) {
                    ev.stopPropagation();
                }
                break;
            case 'ArrowUp':
                ev.stopPropagation();
                ev.preventDefault();
                this._onSelectPreviousSuggestion();
                break;
            case 'ArrowDown':
                ev.stopPropagation();
                ev.preventDefault();
                this._onSelectNextSuggestion();
                break;
            default:
                this._onSuggest();
                break;
        }
    }

    private _onSuggest = debounce(500, async () => {
        const {
            _inputElement: inputElement,
        } = this;

        const text = inputElement.value;
        const suggestions = await this.suggestGloss(text);
        this._applySuggestions(suggestions);
    });

    private _onSelectPreviousSuggestion = () => {
        let index = this._suggestionIndex - 1;

        if (index < 1) {
            index = this._suggestions.length;
        }

        this._suggestionIndex = index;
        this._onSelectSuggestionChanged();
    };

    private _onSelectNextSuggestion = () => {
        let index = this._suggestionIndex + 1;

        if (index > this._suggestions.length) {
            index = 1;
        }

        this._suggestionIndex = index;
        this._onSelectSuggestionChanged();
    };

    private _onSelectSuggestionChanged = () => {
        const {
            _suggestionIndex: index,
            _suggestionListElement: list,
            _suggestionsScroller: container,
        } = this;

        const className = 'GlossCellEditor--selected';

        const existingElement = list.querySelector(`.${className}`);
        if (existingElement) {
            existingElement.classList.remove(className);
        }

        const selectElement = list.querySelector<HTMLLIElement>(`li:nth-child(${index})`);
        if (selectElement) {
            selectElement.classList.add(className);
            // Make sure that the element is visible in the viewport
            container.scrollTop = selectElement.offsetTop - list.offsetTop;
        }
    };

    private _onSuggestionClick = (ev: Event) => {
        ev.preventDefault();
        const target = ev.target as HTMLAnchorElement;
        const glossId = parseInt(target.dataset.glossId, 10);

        const index = this._suggestions.findIndex((s) => s.id === glossId);
        if (index > -1) {
            this._suggestionIndex = index + 1; // index is 1-based.
            this._applySuggestion();
            this._editorParams.api.stopEditing(false); // close the editor as the customer has made their choice.
        }
    };
}