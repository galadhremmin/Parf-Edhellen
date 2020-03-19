import {
    Constants,
    ICellEditorComp,
    ICellEditorParams,
    PopupComponent,
} from '@ag-grid-community/all-modules';

import { ISuggestionEntity } from '@root/connectors/backend/IGlossResourceApi';
import debounce from '@root/utilities/func/debounce';
import { IFragmentGridMetadata } from '../FragmentsGrid._types';

import './GlossCellEditor.scss';

export default class GlossCellEditor extends PopupComponent implements ICellEditorComp {
    private static TEMPLATE = `<div class="ag-input-wrapper" role="presentation">
        <input type="text" list="ag-input-available-values" />
        <ul></ul>
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

        this._inputElement          = this.getGui().querySelector<HTMLInputElement>('input');
        this._suggestionListElement = this.getGui().querySelector<HTMLUListElement>('ul');

        let value = params.value;

        if (params.cellStartedEdit) {
            this.focusAfterAttached = true;

            if (Constants.KEY_BACKSPACE === params.keyPress ||
                Constants.KEY_DELETE    === params.keyPress) {
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
            });
        }

        this.addDestroyableEventListener(this._inputElement, 'keydown', this._onKeyDown);
    }

    public afterGuiAttached() {
        if (this.focusAfterAttached) {
            this._inputElement.focus();
        }
    }

    public focusIn() {
        this._inputElement.focus();
    }

    public getValue() {
        return this._value;
    }

    public isPopup() {
        return true;
    }

    private _applySuggestion() {
        const index = this._suggestionIndex - 1;
        if (index < 0 || index >= this._suggestions.length) {
            return false;
        }

        const suggestion = this._suggestions[index];
        this._value = suggestion.id;
        return true;
    }

    private _onKeyDown = (ev: KeyboardEvent) => {
        const target = ev.target as HTMLInputElement;
        switch (ev.keyCode) {
            case Constants.KEY_ENTER:
                if (target.value.length > 0 && ! this._applySuggestion()) {
                    ev.stopPropagation();
                }
                break;
            case Constants.KEY_UP:
                ev.stopPropagation();
                ev.preventDefault();
                this._onSelectPreviousSuggestion();
                break;
            case Constants.KEY_DOWN:
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
            _suggestionListElement: suggestionListElement,
            _suggestionIndex: index,
        } = this;

        const text = inputElement.value;
        const suggestions = await this.suggestGloss(text);
        const html: string[] = [];

        suggestions.forEach((s) => {
            html.push(
                `<li>
                    <a href="#" class="GlossCellEditor--suggestion" data-gloss-id="${s.id}">
                        <strong>${s.word}</strong> <i>${s.type || ''}</i> “${s.translation}” [${s.source}] (${s.id})
                    </a>
                </li>`,
            );
        });

        this._suggestions = suggestions;
        this._suggestionIndex = 0;

        suggestionListElement.innerHTML = html.join('');
        suggestionListElement.querySelectorAll('a[data-gloss-id]').forEach((a) => {
            a.addEventListener('click', this._onSuggestionClick);
        });
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
        } = this;

        const className = 'GlossCellEditor--selected';

        const existingElement = list.querySelector(`.${className}`);
        if (existingElement) {
            existingElement.classList.remove(className);
        }

        const selectElement = list.querySelector(`li:nth-child(${index})`);
        if (selectElement) {
            selectElement.classList.add(className);
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
        }
    };
}