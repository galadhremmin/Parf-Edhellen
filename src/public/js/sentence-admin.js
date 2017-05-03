webpackJsonp([1],{

/***/ 103:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.setFragmentData = exports.setSentenceData = exports.setFragments = undefined;

var _admin = __webpack_require__(104);

var setFragments = exports.setFragments = function setFragments(fragments) {
    return {
        type: _admin.SET_FRAGMENTS,
        fragments: fragments
    };
};

var setSentenceData = exports.setSentenceData = function setSentenceData(data) {
    return {
        type: _admin.SET_SENTENCE_DATA,
        data: data
    };
};

/**
 * Updates the fragments at the specified indexes with the specified data.
 * @param {Number[]} fragmentIndex 
 * @param {Object} data 
 */
var setFragmentData = exports.setFragmentData = function setFragmentData(indexes, data) {
    return {
        type: _admin.SET_FRAGMENT_DATA,
        indexes: indexes,
        data: data
    };
};

/***/ }),

/***/ 104:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var SET_FRAGMENTS = exports.SET_FRAGMENTS = 'ED_SET_FRAGMENTS';
var SET_FRAGMENT_DATA = exports.SET_FRAGMENT_DATA = 'ED_SET_FRAGMENT_DATA';
var SET_SENTENCE_DATA = exports.SET_SENTENCE_DATA = 'ED_SET_SENTENCE_DATA';

var EDSentenceAdminReducer = function EDSentenceAdminReducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
        name: '',
        source: '',
        language_id: undefined,
        description: '',
        long_description: '',
        is_neologism: false,
        fragments: [],
        id: 0,
        languages: window.EDConfig.languages(),
        loading: false,
        suggestions: undefined
    };
    var action = arguments[1];

    switch (action.type) {
        case SET_FRAGMENTS:
            return _extends({}, state, {
                fragments: action.fragments
            });
            break;

        case SET_SENTENCE_DATA:
            return _extends({}, state, action.data);
            break;

        case SET_FRAGMENT_DATA:
            return _extends({}, state, {
                fragments: state.fragments.map(function (f, index) {
                    if (action.indexes.indexOf(index) === -1) {
                        return f;
                    }

                    var newFragment = _extends({}, f, {
                        translation_id: action.data.translation_id,
                        speech_id: action.data.speech_id,
                        comments: action.data.comments,
                        tengwar: action.data.tengwar,
                        inflections: action.data.inflections.map(function (inflection) {
                            return Object.assign({}, inflection);
                        })
                    });

                    return newFragment;
                })
            });
        default:
            return state;
    }
};

exports.default = EDSentenceAdminReducer;

/***/ }),

/***/ 161:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(33);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _reactRouterDom = __webpack_require__(95);

var _reactRedux = __webpack_require__(17);

var _redux = __webpack_require__(29);

var _reduxThunk = __webpack_require__(34);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _admin = __webpack_require__(104);

var _admin2 = _interopRequireDefault(_admin);

var _edSessionStorageState = __webpack_require__(94);

var _sentenceForm = __webpack_require__(191);

var _sentenceForm2 = _interopRequireDefault(_sentenceForm);

var _fragmentForm = __webpack_require__(189);

var _fragmentForm2 = _interopRequireDefault(_fragmentForm);

var _previewForm = __webpack_require__(190);

var _previewForm2 = _interopRequireDefault(_previewForm);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.addEventListener('load', function () {
    var sentenceDataContainer = document.getElementById('ed-preloaded-sentence');
    var fragmentDataContainer = document.getElementById('ed-preloaded-sentence-fragments');

    var preloadedState = undefined;
    var creating = false;
    if (sentenceDataContainer && fragmentDataContainer) {
        var sentenceData = JSON.parse(sentenceDataContainer.textContent);
        var fragmentData = JSON.parse(fragmentDataContainer.textContent);

        preloadedState = _extends({}, sentenceData, {
            fragments: fragmentData,
            languages: _edConfig2.default.languages()
        });
    } else {
        preloadedState = (0, _edSessionStorageState.loadState)('sentence');
        creating = true;
    }

    var store = (0, _redux.createStore)(_admin2.default, preloadedState, (0, _redux.applyMiddleware)(_reduxThunk2.default));

    if (creating) {
        store.subscribe(function () {
            var state = store.getState();
            (0, _edSessionStorageState.saveState)('sentence', {
                name: state.name,
                source: state.source,
                language_id: state.language_id,
                description: state.description,
                long_description: state.long_description,
                fragments: state.fragments,
                id: state.id
            });
        });
    }

    _reactDom2.default.render(_react2.default.createElement(
        _reactRedux.Provider,
        { store: store },
        _react2.default.createElement(
            _reactRouterDom.MemoryRouter,
            { initialEntries: ['/form', '/fragments', '/preview'], initialIndex: 0 },
            _react2.default.createElement(
                'div',
                null,
                _react2.default.createElement(_reactRouterDom.Route, { path: '/form', component: _sentenceForm2.default }),
                _react2.default.createElement(_reactRouterDom.Route, { path: '/fragments', component: _fragmentForm2.default }),
                _react2.default.createElement(_reactRouterDom.Route, { path: '/preview', component: _previewForm2.default })
            )
        )
    ), document.getElementById('ed-sentence-form'));
});

/***/ }),

/***/ 181:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _reactAutosuggest = __webpack_require__(57);

var _reactAutosuggest2 = _interopRequireDefault(_reactAutosuggest);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDInflectionSelect = function (_React$Component) {
    _inherits(EDInflectionSelect, _React$Component);

    function EDInflectionSelect(props) {
        _classCallCheck(this, EDInflectionSelect);

        var _this = _possibleConstructorReturn(this, (EDInflectionSelect.__proto__ || Object.getPrototypeOf(EDInflectionSelect)).call(this, props));

        _this.state = {
            inflections: [],
            selectedInflections: [],
            groupNames: [],
            value: props.value || '',
            suggestions: []
        };
        return _this;
    }

    _createClass(EDInflectionSelect, [{
        key: 'componentWillMount',
        value: function componentWillMount() {
            var _this2 = this;

            _axios2.default.get(_edConfig2.default.api('inflection')).then(function (resp) {
                return _this2.setLoadedInflections(resp.data);
            });
        }
    }, {
        key: 'setLoadedInflections',
        value: function setLoadedInflections(inflections) {
            var groupNames = Object.keys(inflections);

            groupNames.forEach(function (groupName) {
                inflections[groupName].forEach(function (inflection) {
                    inflection.name = inflection.name.toLocaleLowerCase();
                });
            });

            this.setState({
                inflections: inflections,
                groupNames: groupNames
            });
        }

        /**
         * Sets the selected inflections. These should be retrieved from the server
         * to be considered valid.
         * 
         * @param {Object[]} inflections 
         */

    }, {
        key: 'setValue',
        value: function setValue(selectedInflections) {
            this.setState({
                selectedInflections: selectedInflections,
                value: ''
            });
        }

        /**
         * Gets an array containing the inflections currently selected.
         */

    }, {
        key: 'getValue',
        value: function getValue() {
            return this.state.selectedInflections || [];
        }
    }, {
        key: 'getSuggestions',
        value: function getSuggestions(data) {
            var _this3 = this;

            var name = data.value.toLocaleLowerCase();

            var sections = [];
            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = this.state.groupNames[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var groupName = _step.value;

                    var inflections = this.state.inflections[groupName].filter(function (i) {
                        return i.name.length >= name.length && i.name.substr(0, name.length) === name && _this3.state.selectedInflections.indexOf(i) === -1;
                    } // isn't selected!
                    );

                    if (inflections.length > 0) {
                        sections.push({
                            inflections: inflections,
                            groupName: groupName
                        });
                    }
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            return sections;
        }
    }, {
        key: 'getSuggestionValue',
        value: function getSuggestionValue(suggestion) {
            return suggestion.name;
        }
    }, {
        key: 'getSectionSuggestions',
        value: function getSectionSuggestions(section) {
            return section.inflections;
        }
    }, {
        key: 'renderSuggestion',
        value: function renderSuggestion(suggestion) {
            return _react2.default.createElement(
                'span',
                null,
                suggestion.name
            );
        }
    }, {
        key: 'renderSectionTitle',
        value: function renderSectionTitle(section) {
            return _react2.default.createElement(
                'strong',
                null,
                section.groupName
            );
        }
    }, {
        key: 'onInflectionChange',
        value: function onInflectionChange(ev, data) {
            this.setState({
                value: data.newValue
            });
        }
    }, {
        key: 'onRemoveInflectionClick',
        value: function onRemoveInflectionClick(ev, inflection) {
            this.setState({
                selectedInflections: this.state.selectedInflections.filter(function (i) {
                    return i.id !== inflection.id;
                })
            });
        }
    }, {
        key: 'onSuggestionSelect',
        value: function onSuggestionSelect(ev, data) {
            ev.preventDefault();
            this.setValue([].concat(_toConsumableArray(this.state.selectedInflections), [data.suggestion]));
        }
    }, {
        key: 'onSuggestionsFetchRequest',
        value: function onSuggestionsFetchRequest(data) {
            this.setState({
                suggestions: this.getSuggestions(data)
            });
        }
    }, {
        key: 'onSuggestionsClearRequest',
        value: function onSuggestionsClearRequest() {
            this.setState({
                suggestions: []
            });
        }
    }, {
        key: 'render',
        value: function render() {
            var _this4 = this;

            var inputProps = {
                placeholder: 'Search for an inflection',
                value: this.state.value,
                name: this.props.componentName,
                id: this.props.componentId,
                onChange: this.onInflectionChange.bind(this)
            };

            return _react2.default.createElement(
                'div',
                { className: 'ed-inflection-select' },
                _react2.default.createElement(
                    'div',
                    null,
                    _react2.default.createElement(_reactAutosuggest2.default, {
                        alwaysRenderSuggestions: false // set to _true_ to view all.
                        , multiSection: true,
                        suggestions: this.state.suggestions,
                        onSuggestionsFetchRequested: this.onSuggestionsFetchRequest.bind(this),
                        onSuggestionsClearRequested: this.onSuggestionsClearRequest.bind(this),
                        onSuggestionSelected: this.onSuggestionSelect.bind(this),
                        getSuggestionValue: this.getSuggestionValue.bind(this),
                        renderSuggestion: this.renderSuggestion.bind(this),
                        renderSectionTitle: this.renderSectionTitle.bind(this),
                        getSectionSuggestions: this.getSectionSuggestions.bind(this),
                        inputProps: inputProps })
                ),
                _react2.default.createElement(
                    'div',
                    null,
                    this.state.selectedInflections.map(function (i) {
                        return _react2.default.createElement(
                            'span',
                            { key: i.id },
                            _react2.default.createElement(
                                'a',
                                { className: 'label label-default selected-inflection',
                                    onClick: function onClick(e) {
                                        return _this4.onRemoveInflectionClick(e, i);
                                    },
                                    title: 'Press on the label (' + i.name + ') to remove it.' },
                                i.name
                            ),
                            ' '
                        );
                    })
                )
            );
        }
    }]);

    return EDInflectionSelect;
}(_react2.default.Component);

EDInflectionSelect.defaultProps = {
    componentName: 'inflection',
    componentId: undefined,
    value: 0
};

exports.default = EDInflectionSelect;

/***/ }),

/***/ 182:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDSpeechSelect = function (_React$Component) {
    _inherits(EDSpeechSelect, _React$Component);

    function EDSpeechSelect(props) {
        _classCallCheck(this, EDSpeechSelect);

        var _this = _possibleConstructorReturn(this, (EDSpeechSelect.__proto__ || Object.getPrototypeOf(EDSpeechSelect)).call(this, props));

        _this.state = {
            typesOfSpeech: [],
            value: props.value || 0
        };
        return _this;
    }

    _createClass(EDSpeechSelect, [{
        key: 'componentWillMount',
        value: function componentWillMount() {
            var _this2 = this;

            _axios2.default.get(_edConfig2.default.api('speech')).then(function (resp) {
                return _this2.setLoadedTypesOfSpeech(resp.data);
            });
        }
    }, {
        key: 'setLoadedTypesOfSpeech',
        value: function setLoadedTypesOfSpeech(typesOfSpeech) {
            this.setState({
                typesOfSpeech: typesOfSpeech
            });
        }

        /**
         * Sets the type of speech currently selected. The object must be retrieved
         * from the server to be considered valid.
         * @param {Object} value 
         */

    }, {
        key: 'setValue',
        value: function setValue(value) {
            if (!value) {
                value = 0;
            }

            this.setState({
                value: value
            });
        }

        /**
         * Gets the component's current value.
         */

    }, {
        key: 'getValue',
        value: function getValue() {
            return this.state.value;
        }
    }, {
        key: 'onSpeechChange',
        value: function onSpeechChange(ev) {
            this.setValue(parseInt(ev.target.value, 10));

            if (typeof this.props.onChange === 'function') {
                this.props.onChange(ev);
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var typesOfSpeech = this.state.typesOfSpeech || [];
            return _react2.default.createElement(
                'select',
                { onChange: this.onSpeechChange.bind(this),
                    name: this.props.componentName,
                    id: this.props.componentId,
                    value: this.state.value,
                    className: (0, _classnames2.default)('form-control', { 'disabled': this.state.typesOfSpeech.length < 1 }) },
                _react2.default.createElement('option', { value: 0 }),
                this.state.typesOfSpeech.map(function (s) {
                    return _react2.default.createElement(
                        'option',
                        { key: s.id, value: s.id },
                        s.name
                    );
                })
            );
        }
    }]);

    return EDSpeechSelect;
}(_react2.default.Component);

EDSpeechSelect.defaultProps = {
    componentName: 'speech',
    componentId: undefined,
    value: 0
};

exports.default = EDSpeechSelect;

/***/ }),

/***/ 183:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _reactAutosuggest = __webpack_require__(57);

var _reactAutosuggest2 = _interopRequireDefault(_reactAutosuggest);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDTranslationSelect = function (_React$Component) {
    _inherits(EDTranslationSelect, _React$Component);

    function EDTranslationSelect(props) {
        _classCallCheck(this, EDTranslationSelect);

        var _this = _possibleConstructorReturn(this, (EDTranslationSelect.__proto__ || Object.getPrototypeOf(EDTranslationSelect)).call(this, props));

        _this.state = {
            suggestions: props.suggestions || [],
            value: props.value || undefined,
            word: ''
        };
        return _this;
    }

    _createClass(EDTranslationSelect, [{
        key: 'componentWillReceiveProps',
        value: function componentWillReceiveProps(props) {
            if (Array.isArray(props.suggestions)) {
                this.setState({
                    suggestions: props.suggestions,
                    suggestionsFor: undefined
                });
            }
        }

        /**
         * Sets the word currently selected.
         * @param {Object} value - Translation object
         */

    }, {
        key: 'setValue',
        value: function setValue(value) {
            this.setState({
                value: value,
                word: value ? value.word : ''
            });
        }

        /**
         * Gets the word currently selected.
         */

    }, {
        key: 'getValue',
        value: function getValue() {
            return this.state.value;
        }
    }, {
        key: 'onWordChange',
        value: function onWordChange(ev, data) {
            this.setState({
                word: data.newValue,
                value: this.state.value && this.state.value.word === data.newValue ? this.state.value : undefined
            });

            //this.setValue(data.newValue);

            if (typeof this.props.onChange === 'function') {
                this.props.onChange(ev);
            }
        }
    }, {
        key: 'onSuggestionsFetchRequest',
        value: function onSuggestionsFetchRequest(data) {
            var _this2 = this;

            var word = (data.value || '').toLocaleLowerCase();

            // already fetching suggestions?
            if (this.loading || /^\s*$/.test(word) || this.state.suggestionsFor === word) {
                return;
            }

            // Throttle search requests, to prevent them from occurring too often.
            if (this.searchDelay) {
                window.clearTimeout(this.searchDelay);
                this.searchDelay = 0;
            }

            this.searchDelay = window.setTimeout(function () {
                _this2.searchDelay = 0;
                _this2.loading = true;

                // Retrieve suggestions for the specified word.
                _axios2.default.post(_edConfig2.default.api('book/suggest'), {
                    words: [word],
                    language_id: _this2.props.languageId,
                    inexact: true
                }).then(function (resp) {
                    _this2.setState({
                        suggestions: resp.data[word] || [],
                        suggestionsFor: word
                    });

                    _this2.loading = false;
                });
            }, 800);
        }
    }, {
        key: 'onSuggestionsClearRequest',
        value: function onSuggestionsClearRequest() {
            this.setState({
                suggestions: !Array.isArray(this.props.suggestions) || this.props.suggestions === this.state.suggestions ? [] : this.props.suggestions
            });
        }
    }, {
        key: 'onSuggestionSelect',
        value: function onSuggestionSelect(ev, data) {
            ev.preventDefault();
            this.setState({
                value: data.suggestion || undefined
            });
        }
    }, {
        key: 'getSuggestionValue',
        value: function getSuggestionValue(suggestion) {
            return suggestion.word;
        }
    }, {
        key: 'renderInput',
        value: function renderInput(inputProps) {
            var valid = !!this.state.value;
            return _react2.default.createElement(
                'div',
                { className: (0, _classnames2.default)('input-group', { 'has-warning': !valid, 'has-success': valid }) },
                _react2.default.createElement('input', inputProps),
                _react2.default.createElement(
                    'div',
                    { className: 'input-group-addon' },
                    _react2.default.createElement('span', { className: (0, _classnames2.default)('glyphicon', { 'glyphicon-ok': valid, 'glyphicon-exclamation-sign': !valid }) })
                )
            );
        }
    }, {
        key: 'renderSuggestion',
        value: function renderSuggestion(suggestion) {
            return _react2.default.createElement(
                'div',
                { title: suggestion.comments },
                _react2.default.createElement(
                    'strong',
                    null,
                    suggestion.word
                ),
                ': ',
                suggestion.type ? _react2.default.createElement(
                    'em',
                    null,
                    suggestion.type + ' '
                ) : '',
                suggestion.translation,
                ' ',
                '[',
                suggestion.source,
                ']',
                _react2.default.createElement('br', null),
                _react2.default.createElement(
                    'small',
                    null,
                    'by ',
                    _react2.default.createElement(
                        'em',
                        null,
                        suggestion.account_name
                    ),
                    ' ',
                    suggestion.translation_group_name ? _react2.default.createElement(
                        'span',
                        null,
                        '(',
                        _react2.default.createElement(
                            'em',
                            null,
                            suggestion.translation_group_name
                        ),
                        ')'
                    ) : ''
                )
            );
        }
    }, {
        key: 'render',
        value: function render() {
            var inputProps = {
                placeholder: 'Search for a suitable translation',
                value: this.state.word,
                name: this.props.componentName,
                id: this.props.componentId,
                onChange: this.onWordChange.bind(this)
            };

            return _react2.default.createElement(
                'div',
                null,
                _react2.default.createElement(
                    'div',
                    null,
                    _react2.default.createElement(_reactAutosuggest2.default, {
                        alwaysRenderSuggestions: false,
                        multiSection: false,
                        suggestions: this.state.suggestions,
                        onSuggestionsFetchRequested: this.onSuggestionsFetchRequest.bind(this),
                        onSuggestionsClearRequested: this.onSuggestionsClearRequest.bind(this),
                        onSuggestionSelected: this.onSuggestionSelect.bind(this),
                        getSuggestionValue: this.getSuggestionValue.bind(this),
                        renderInputComponent: this.renderInput.bind(this),
                        renderSuggestion: this.renderSuggestion.bind(this),
                        inputProps: inputProps })
                )
            );
        }
    }]);

    return EDTranslationSelect;
}(_react2.default.Component);

EDTranslationSelect.defaultProps = {
    componentName: 'word',
    componentId: undefined,
    value: 0,
    languageId: 0
};

exports.default = EDTranslationSelect;

/***/ }),

/***/ 184:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});


/**
 * Transcribes the specified text body to tengwar using the parmaite font.
 * @param {string} text 
 * @param {string} mode 
 */
var transcribe = exports.transcribe = function transcribe(text, mode) {
    if (!window.EDTengwarInitialized || !window.EDTengwarInitialized.hasOwnProperty(mode)) {
        Glaemscribe.resource_manager.load_modes(mode);

        var initialized = window.EDTengwarInitialized || {};
        initialized[mode] = true;

        window.EDTengwarInitialized = initialized;
    }

    var trascriber = Glaemscribe.resource_manager.loaded_modes[mode];
    var charset = Glaemscribe.resource_manager.loaded_charsets['tengwar_ds_parmaite'];
    if (!trascriber) {
        return undefined;
    }

    // Transcribe using Glaemscribe transcriber for the specified mode
    // The result is an array with three elements: 
    // 0th element: whether the transcription was successful (true/false)
    // 1th element: transcription result
    // 2th element: debug data
    var result = trascriber.transcribe(text, charset);
    if (!result[0]) {
        return undefined; // failed!
    }

    // Return the transcription results
    return result[1].trim();
};

/***/ }),

/***/ 189:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _reactRedux = __webpack_require__(17);

var _reactRouter = __webpack_require__(11);

var _smoothscrollPolyfill = __webpack_require__(46);

var _admin = __webpack_require__(103);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _edForm = __webpack_require__(55);

var _tengwar = __webpack_require__(184);

var _markdownEditor = __webpack_require__(45);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

var _errorList = __webpack_require__(44);

var _errorList2 = _interopRequireDefault(_errorList);

var _speechSelect = __webpack_require__(182);

var _speechSelect2 = _interopRequireDefault(_speechSelect);

var _inflectionSelect = __webpack_require__(181);

var _inflectionSelect2 = _interopRequireDefault(_inflectionSelect);

var _translationSelect = __webpack_require__(183);

var _translationSelect2 = _interopRequireDefault(_translationSelect);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDFragmentForm = function (_EDStatefulFormCompon) {
    _inherits(EDFragmentForm, _EDStatefulFormCompon);

    function EDFragmentForm(props) {
        _classCallCheck(this, EDFragmentForm);

        // Reconstruct the phrase from the sentence fragments. Only one rule needs to 
        // be observed: add a space in front of the fragment, unless it contains a
        // interpunctuation character.
        var _this = _possibleConstructorReturn(this, (EDFragmentForm.__proto__ || Object.getPrototypeOf(EDFragmentForm)).call(this, props));

        var phrase = '';
        if (Array.isArray(props.fragments)) {
            phrase = props.fragments.map(function (f, i) {
                return (i === 0 || f.interpunctuation ? '' : ' ') + f.fragment;
            }).join('');
        }

        _this.state = {
            phrase: phrase,
            editingFragmentIndex: -1,
            erroneousIndexes: []
        };
        return _this;
    }

    _createClass(EDFragmentForm, [{
        key: 'createFragment',
        value: function createFragment(fragment, interpunctuation) {
            return {
                fragment: fragment,
                interpunctuation: interpunctuation
            };
        }
    }, {
        key: 'editFragment',
        value: function editFragment(fragmentIndex, additionalParams) {
            var _this2 = this;

            if (additionalParams === undefined) {
                additionalParams = {};
            }

            if (fragmentIndex < -1 || fragmentIndex >= this.props.fragments.length) {
                fragmentIndex = -1;
            }

            if (fragmentIndex > -1) {
                var data = this.props.fragments[fragmentIndex];

                var promise = void 0;
                if (data.translation_id) {
                    promise = _axios2.default.get(_edConfig2.default.api('book/translate/' + data.translation_id)).then(function (resp) {
                        if (!resp.data.sections || !resp.data.sections.length || !resp.data.sections[0].glosses || resp.data.sections[0].glosses.length < 1) {
                            return undefined;
                        }

                        return resp.data.sections[0].glosses[0];
                    });
                } else {
                    promise = Promise.resolve(undefined);
                }

                promise.then(function (translation) {
                    _this2.translationInput.setValue(translation);
                    _this2.speechInput.setValue(data.speech_id);
                    _this2.inflectionInput.setValue(data.inflections ? data.inflections : []);
                    _this2.tengwarInput.value = data.tengwar || '';
                    _this2.commentsInput.setValue(data.comments || '');
                });
            }

            this.setState(_extends({}, additionalParams, {
                editingFragmentIndex: fragmentIndex
            }));
        }
    }, {
        key: 'scrollToForm',
        value: function scrollToForm() {
            // add a little delay because it's actually useful in this situation
            window.setTimeout(function () {
                document.querySelector('.fragment-admin-form').scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 250);
        }
    }, {
        key: 'submit',
        value: function submit() {
            // validate all fragments
            var fragments = this.props.fragments;
            _axios2.default.post('/admin/sentence/validate-fragment', { fragments: fragments }).then(this.onFragmentsValid.bind(this), this.onFragmentsInvalid.bind(this));
        }
    }, {
        key: 'onPreviousClick',
        value: function onPreviousClick(ev) {
            ev.preventDefault();
            this.props.history.goBack();
        }
    }, {
        key: 'onPhraseChange',
        value: function onPhraseChange(ev) {
            var _this3 = this;

            ev.preventDefault();

            var currentFragments = this.props.fragments || [];
            var newFragments = this.state.phrase.replace(/\r\n/g, "\n").split(' ').map(function (f) {
                return _this3.createFragment(f);
            });

            for (var i = 0; i < newFragments.length; i += 1) {
                var data = newFragments[i];
                if (data.interpunctuation) {
                    continue;
                }

                // Find interpunctuation and new line fragments, and remove them from the actual
                // word fragment. These should be registered as fragments of their own.
                for (var fi = 0; fi < data.fragment.length; fi += 1) {
                    if (!/^[,\.!\?\s]$/.test(data.fragment[fi])) {
                        continue;
                    }

                    // Should the fragment be inserted in front of the current fragment or after it?
                    // This is determined by looking at the cursor's position (_fi_). If it is at
                    // in its initial position (= 0) then the interpunctutation fragment should be
                    // placed in front of it, otherwise after. 
                    var insertAt = fi === 0 ? i : i + 1;
                    newFragments.splice(insertAt, 0, this.createFragment(data.fragment[fi], true));

                    // are there more of the fragment after the interpunctuation?
                    if (fi + 1 < data.fragment.length) {
                        newFragments.splice(insertAt + 1, 0, this.createFragment(data.fragment.substr(fi + 1)));
                    }

                    if (fi > 0) {
                        data.fragment = data.fragment.substr(0, fi);

                        i -= 1;
                    } else {
                        newFragments.splice(insertAt + 1, 1);

                        i -= 2;
                    }

                    break;
                }
            }

            var words = [];

            var _loop = function _loop(_i) {
                var data = newFragments[_i];
                var lowerFragment = data.fragment.toLocaleLowerCase();
                var existingFragment = currentFragments.find(function (f) {
                    return f.fragment.toLocaleLowerCase() === lowerFragment;
                }) || undefined;

                if (existingFragment !== undefined) {
                    // overwrite the fragment with the existing fragment, as it might contain more data
                    newFragments[_i] = _extends({}, existingFragment, { fragment: data.fragment });
                }

                if (!newFragments[_i].interpunctuation) {
                    words.push(newFragments[_i].fragment);
                }
            };

            for (var _i = 0; _i < newFragments.length; _i += 1) {
                _loop(_i);
            }

            // We can't be editing a fragment.
            this.editFragment(-1, {
                errors: undefined,
                erroneousIndexes: []
            });

            // Make the fragments permanent (in the client) by dispatching the fragments to the Redux component.
            this.props.dispatch((0, _admin.setFragments)(newFragments));
        }
    }, {
        key: 'onFragmentClick',
        value: function onFragmentClick(data) {
            var fragmentIndex = this.props.fragments.indexOf(data);
            this.editFragment(fragmentIndex);
        }
    }, {
        key: 'onTranscribeClick',
        value: function onTranscribeClick(ev) {
            var _this4 = this;

            ev.preventDefault();

            var language = this.props.languages.find(function (l) {
                return l.id === _this4.props.language_id;
            });
            var data = this.props.fragments[this.state.editingFragmentIndex];

            var transcription = (0, _tengwar.transcribe)(data.fragment, language.tengwar_mode, false);
            if (transcription) {
                this.tengwarInput.value = transcription;
            } else {
                errors = ['Unfortunately, the transcription service does not support ' + language.name + '.'];
                this.setState({
                    errors: errors
                });
            }
        }
    }, {
        key: 'onFragmentSaveClick',
        value: function onFragmentSaveClick(ev) {
            var _this5 = this;

            ev.preventDefault();

            var fragment = this.props.fragments[this.state.editingFragmentIndex];
            var translation = this.translationInput.getValue();
            var inflections = this.inflectionInput.getValue();
            var speech_id = this.speechInput.getValue();
            var comments = this.commentsInput.getValue();
            var tengwar = this.tengwarInput.value;

            var fragmentData = {
                translation_id: translation ? translation.id : undefined,
                speech_id: speech_id,
                inflections: inflections,
                comments: comments,
                tengwar: tengwar
            };

            // If the 'apply to similar words' checkbox is checked, make an array
            // with the indexes of all fragments similar to the one currently being
            // edited. By using the reduce function, the fragments array is reduced
            // to an array with indexes. It works like a filter and adapter at the 
            // same time.
            var indexes = this.applyToSimilarCheckbox.checked ? this.props.fragments.reduce(function (accumulator, f, i) {
                if (f.fragment !== fragment.fragment) {
                    return accumulator; // the fragments are dissimilar.
                }

                return [].concat(_toConsumableArray(accumulator), [i]); // fragments are similar = add the index
            }, [])
            // If the checkbox isn't checked, just update the fragment currently being edited.
            : [this.state.editingFragmentIndex];

            this.props.dispatch((0, _admin.setFragmentData)(indexes, fragmentData));

            if (this.state.erroneousIndexes.length === 0) {
                // go to the next fragment in the collection, but skip over interpunuctations.
                var nextIndex = this.state.editingFragmentIndex + 1;
                while (nextIndex < this.props.fragments.length) {
                    fragmentData = this.props.fragments[nextIndex];

                    if (!fragmentData.interpunctuation) {
                        break;
                    }

                    // interpuncutations -- skip
                    nextIndex += 1;
                }

                if (nextIndex < this.props.fragments.length) {
                    // the next index lies within the bounds of the array. Execute in a new
                    // thread to leave the event handler.
                    window.setTimeout(function () {
                        _this5.editFragment(nextIndex);
                        _this5.scrollToForm(); // for mobile devices
                    }, 0);
                } else {
                    // if the next index is outside the bounds of the array ...
                    this.editFragment(-1); // ... consider editing done - close the dialogue!
                }
            } else {
                // submit the form continously when there are erroneous indexes, 
                // as it suggests that the client has been trying to subbmit the form
                // previously but got denied because of a server-side validation error.
                // 
                // By submitting the form, the server side will re-evaluate the content
                // with the new data supplied by the client.
                //
                // Execute the submission on a new thread.
                window.setTimeout(function () {
                    _this5.submit();
                }, 0);
            }
        }
    }, {
        key: 'onFragmentsValid',
        value: function onFragmentsValid(response) {
            this.setState({
                errors: undefined,
                erroneousIndexes: []
            });

            this.props.history.goForward();
        }
    }, {
        key: 'onFragmentsInvalid',
        value: function onFragmentsInvalid(result) {
            if (result.response.status !== _edConfig2.default.apiValidationErrorStatusCode) {
                return; // unknown error code
            }

            var errors = [];
            var erroneousIndexes = [];
            for (var erroneousElementName in result.response.data) {
                var parts = /^fragments.([0-9]+).([a-zA-Z0-9_]+)/.exec(erroneousElementName);
                if (parts.length < 3) {
                    continue; // unsupported response format
                }

                var index = parseInt(parts[1], 10);
                var missing = parts[2];

                if (index < 0 || index >= this.props.fragments.length) {
                    continue; // mismatch server/client, probably due to lagging synchronization
                }

                if (erroneousIndexes.indexOf(index) === -1) {
                    erroneousIndexes.push(index);
                }

                var fragmentData = this.props.fragments[index];
                errors.push(fragmentData.fragment + ' (' + (index + 1) + '-th word) is missing or has an invalid ' + missing + '.');
            }

            if (erroneousIndexes.length > 0) {
                this.setState({
                    errors: errors,
                    erroneousIndexes: erroneousIndexes
                });

                this.editFragment(erroneousIndexes[0]);
                this.scrollToForm();
            }
        }
    }, {
        key: 'onFragmentCancel',
        value: function onFragmentCancel(ev) {
            ev.preventDefault();
            this.editFragment(-1);
        }
    }, {
        key: 'onSubmit',
        value: function onSubmit(ev) {
            ev.preventDefault();
            if (this.state.erroneousIndexes.length === 0) {
                this.submit();
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var _this6 = this;

            return _react2.default.createElement(
                'form',
                { onSubmit: this.onSubmit.bind(this) },
                _react2.default.createElement(
                    'p',
                    null,
                    'This is the second step of a total of three steps. Here you will write down your phrase and attach grammatical meaning and analysis to words of your choosing. Please try to be as thorough as possible as it will make the database more useful for everyone.'
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-phrase', className: 'control-label' },
                        'Phrase'
                    ),
                    _react2.default.createElement('textarea', { id: 'ed-sentence-phrase', className: 'form-control', name: 'phrase', rows: '8',
                        value: this.state.phrase, onChange: this.onChange.bind(this) })
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'text-right' },
                    _react2.default.createElement(
                        'button',
                        { className: 'btn btn-primary', onClick: this.onPhraseChange.bind(this) },
                        _react2.default.createElement('span', { className: 'glyphicon glyphicon-refresh' }),
                        ' Update phrase'
                    )
                ),
                _react2.default.createElement(
                    'p',
                    null,
                    _react2.default.createElement(
                        'strong',
                        null,
                        'Word definitions'
                    )
                ),
                _react2.default.createElement(
                    'p',
                    null,
                    'Green words are linked to words in the dictionary, whereas red words are not. Please link all words before proceeding to the next step.'
                ),
                _react2.default.createElement(
                    'p',
                    null,
                    this.props.fragments.map(function (f, i) {
                        return _react2.default.createElement(EDFragment, { key: i,
                            fragment: f,
                            selected: i === _this6.state.editingFragmentIndex,
                            erroneous: _this6.state.erroneousIndexes.indexOf(i) > -1,
                            onClick: _this6.onFragmentClick.bind(_this6) });
                    })
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'fragment-admin-form' },
                    this.state.editingFragmentIndex > -1 ? this.props.loading ? _react2.default.createElement(
                        'div',
                        null,
                        _react2.default.createElement('div', { className: 'sk-spinner sk-spinner-pulse' }),
                        _react2.default.createElement(
                            'p',
                            { className: 'text-center' },
                            _react2.default.createElement(
                                'em',
                                null,
                                'Loading ...'
                            )
                        )
                    ) : _react2.default.createElement(
                        'div',
                        { className: 'well' },
                        _react2.default.createElement(_errorList2.default, { errors: this.state.errors }),
                        _react2.default.createElement(
                            'div',
                            { className: 'form-group' },
                            _react2.default.createElement(
                                'label',
                                { htmlFor: 'ed-sentence-fragment-word', className: 'control-label' },
                                'Word'
                            ),
                            _react2.default.createElement(_translationSelect2.default, { componentId: 'ed-sentence-fragment-word', languageId: this.props.language_id,
                                suggestions: this.props.suggestions ? this.props.suggestions[this.props.fragments[this.state.editingFragmentIndex].fragment] : [],
                                ref: function ref(input) {
                                    return _this6.translationInput = input;
                                } })
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'form-group' },
                            _react2.default.createElement(
                                'label',
                                { htmlFor: 'ed-sentence-fragment-tengwar', className: 'control-label' },
                                'Tengwar'
                            ),
                            _react2.default.createElement(
                                'div',
                                { className: 'input-group' },
                                _react2.default.createElement('input', { id: 'ed-sentence-fragment-tengwar', className: 'form-control tengwar', type: 'text',
                                    ref: function ref(input) {
                                        return _this6.tengwarInput = input;
                                    } }),
                                _react2.default.createElement(
                                    'div',
                                    { className: 'input-group-addon' },
                                    _react2.default.createElement(
                                        'a',
                                        { href: '#', onClick: this.onTranscribeClick.bind(this) },
                                        'Transcribe'
                                    )
                                )
                            )
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'form-group' },
                            _react2.default.createElement(
                                'label',
                                { htmlFor: 'ed-sentence-fragment-speech', className: 'control-label' },
                                'Type of speech'
                            ),
                            _react2.default.createElement(_speechSelect2.default, { componentId: 'ed-sentence-fragment-speech',
                                ref: function ref(input) {
                                    return _this6.speechInput = input;
                                } })
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'form-group' },
                            _react2.default.createElement(
                                'label',
                                { htmlFor: 'ed-sentence-fragment-inflections', className: 'control-label' },
                                'Inflection(s)'
                            ),
                            _react2.default.createElement(_inflectionSelect2.default, { componentId: 'ed-sentence-fragment-inflections',
                                ref: function ref(input) {
                                    return _this6.inflectionInput = input;
                                } })
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'form-group' },
                            _react2.default.createElement(
                                'label',
                                { htmlFor: 'ed-sentence-fragment-comments', className: 'control-label' },
                                'Comments'
                            ),
                            _react2.default.createElement(_markdownEditor2.default, { componentId: 'ed-sentence-fragment-comments', rows: 4,
                                ref: function ref(input) {
                                    return _this6.commentsInput = input;
                                } })
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'form-group' },
                            _react2.default.createElement(
                                'div',
                                { className: 'checkbox' },
                                _react2.default.createElement(
                                    'label',
                                    null,
                                    _react2.default.createElement('input', { type: 'checkbox', ref: function ref(input) {
                                            return _this6.applyToSimilarCheckbox = input;
                                        } }),
                                    ' Apply changes to similar words.'
                                )
                            )
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'text-right' },
                            _react2.default.createElement(
                                'div',
                                { className: 'btn-group' },
                                _react2.default.createElement(
                                    'button',
                                    { className: 'btn btn-default', onClick: this.onFragmentCancel.bind(this) },
                                    'Cancel'
                                ),
                                _react2.default.createElement(
                                    'button',
                                    { className: 'btn btn-primary', onClick: this.onFragmentSaveClick.bind(this) },
                                    'Save and go forward'
                                )
                            )
                        )
                    ) : ''
                ),
                _react2.default.createElement(
                    'nav',
                    null,
                    _react2.default.createElement(
                        'ul',
                        { className: 'pager' },
                        _react2.default.createElement(
                            'li',
                            { className: 'previous' },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: this.onPreviousClick.bind(this) },
                                '\u2190 Previous step'
                            )
                        ),
                        _react2.default.createElement(
                            'li',
                            { className: (0, _classnames2.default)('next', { 'disabled': this.state.erroneousIndexes.length > 0 }) },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: this.onSubmit.bind(this) },
                                'Next step \u2192'
                            )
                        )
                    )
                )
            );
        }
    }]);

    return EDFragmentForm;
}(_edForm.EDStatefulFormComponent);

var EDFragment = function (_React$Component) {
    _inherits(EDFragment, _React$Component);

    function EDFragment() {
        _classCallCheck(this, EDFragment);

        return _possibleConstructorReturn(this, (EDFragment.__proto__ || Object.getPrototypeOf(EDFragment)).apply(this, arguments));
    }

    _createClass(EDFragment, [{
        key: 'onFragmentClick',
        value: function onFragmentClick(ev) {
            var _this8 = this;

            ev.preventDefault();

            if (this.props.onClick) {
                window.setTimeout(function () {
                    return _this8.props.onClick(_this8.props.fragment);
                }, 0);
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var data = this.props.fragment;
            var selected = this.props.selected;
            var erroneous = this.props.erroneous;

            if (data.interpunctuation) {
                if (/^[\n]+$/.test(data.fragment)) {
                    return _react2.default.createElement('br', null);
                }

                return _react2.default.createElement(
                    'span',
                    null,
                    data.fragment
                );
            }

            return _react2.default.createElement(
                'span',
                null,
                ' ',
                _react2.default.createElement(
                    'a',
                    { href: '#', onClick: this.onFragmentClick.bind(this),
                        className: (0, _classnames2.default)('label', 'ed-sentence-fragment', {
                            'label-success': !!data.translation_id && !selected && !erroneous,
                            'label-warning': erroneous,
                            'label-danger': !data.translation_id && !selected,
                            'label-primary': selected
                        }) },
                    selected ? _react2.default.createElement(
                        'span',
                        null,
                        _react2.default.createElement('span', { className: 'glyphicon glyphicon-pencil' }),
                        ' '
                    ) : erroneous ? _react2.default.createElement(
                        'span',
                        null,
                        _react2.default.createElement('span', { className: 'glyphicon glyphicon-warning-sign' }),
                        ' '
                    ) : '',
                    data.fragment
                )
            );
        }
    }]);

    return EDFragment;
}(_react2.default.Component);

EDFragment.defaultProps = {
    selected: false,
    erroneous: false,
    fragment: {}
};

var mapStateToProps = function mapStateToProps(state) {
    return {
        languages: state.languages,
        language_id: state.language_id,
        fragments: state.fragments,
        suggestions: state.suggestions,
        loading: state.loading
    };
};

exports.default = (0, _reactRouter.withRouter)((0, _reactRedux.connect)(mapStateToProps)(EDFragmentForm));

/***/ }),

/***/ 190:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _reactRouter = __webpack_require__(11);

var _redux = __webpack_require__(29);

var _reactRedux = __webpack_require__(17);

var _reduxThunk = __webpack_require__(34);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _htmlToReact = __webpack_require__(22);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _errorList = __webpack_require__(44);

var _errorList2 = _interopRequireDefault(_errorList);

var _reducers = __webpack_require__(36);

var _reducers2 = _interopRequireDefault(_reducers);

var _fragmentExplorer = __webpack_require__(60);

var _fragmentExplorer2 = _interopRequireDefault(_fragmentExplorer);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDPreviewForm = function (_React$Component) {
    _inherits(EDPreviewForm, _React$Component);

    function EDPreviewForm(props) {
        _classCallCheck(this, EDPreviewForm);

        var _this = _possibleConstructorReturn(this, (EDPreviewForm.__proto__ || Object.getPrototypeOf(EDPreviewForm)).call(this, props));

        _this.state = {
            longDescription: undefined,
            loading: true
        };
        return _this;
    }

    _createClass(EDPreviewForm, [{
        key: 'componentWillMount',
        value: function componentWillMount() {
            var markdowns = {};

            var longDescription = this.props.sentenceLongDescription;
            if (longDescription && !/^\s*$/.test(longDescription)) {
                markdowns['long_description'] = longDescription;
            }

            for (var i = 0; i < this.props.fragments.length; i += 1) {
                var data = this.props.fragments[i];

                if (!/^\s*$/.test(data.comments)) {
                    markdowns['fragment-' + i] = data.comments;
                }
            }

            if (Object.keys(markdowns).length > 0) {
                _axios2.default.post(_edConfig2.default.api('utility/markdown'), { markdowns: markdowns }).then(this.onHtmlReceive.bind(this));
            } else {
                this.createStore(this.props.fragments);
            }
        }
    }, {
        key: 'createStore',
        value: function createStore(fragments) {
            this.previewStore = (0, _redux.createStore)(_reducers2.default, { fragments: fragments }, (0, _redux.applyMiddleware)(_reduxThunk2.default));

            this.setState({
                loading: false
            });
        }
    }, {
        key: 'onHtmlReceive',
        value: function onHtmlReceive(resp) {
            // dirty deep copy to ensure loss of all references!
            var fragments = JSON.parse(JSON.stringify(this.props.fragments));
            var keys = Object.keys(resp.data);

            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = keys[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var key = _step.value;

                    if (key === 'long_description') {
                        this.setState({
                            longDescription: resp.data[key]
                        });
                    } else if (key.substr(0, 9) === 'fragment-') {
                        var index = parseInt(key.substr(9), 10);
                        fragments[index].comments = resp.data[key];
                    }
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            this.createStore(fragments);
        }
    }, {
        key: 'onPreviousClick',
        value: function onPreviousClick(ev) {
            ev.preventDefault();
            this.props.history.goBack();
        }
    }, {
        key: 'onSubmit',
        value: function onSubmit(ev) {
            ev.preventDefault();

            var props = this.props;
            var payload = {
                id: props.sentenceId,
                name: props.sentenceName,
                source: props.sentenceSource,
                language_id: props.sentenceLanguageId,
                description: props.sentenceDescription,
                long_description: props.sentenceLongDescription,
                is_neologism: props.sentenceIsNeologism,
                fragments: props.fragments
            };

            if (payload.id) {
                _axios2.default.put('/admin/sentence/' + payload.id, payload).then(onCreateResponse.bind(this), onFailResponse.bind(this));
            } else {
                _axios2.default.post('/admin/sentence', payload).then(onCreateResponse.bind(this), onFailResponse.bind(this));
            }
        }
    }, {
        key: 'onCreateResponse',
        value: function onCreateResponse(response) {}
    }, {
        key: 'onFailResponse',
        value: function onFailResponse(response) {
            // what to do here?? display errors?
        }
    }, {
        key: 'render',
        value: function render() {
            var longDescription = undefined;
            if (this.state.longDescription) {
                var parser = new _htmlToReact.Parser();
                longDescription = parser.parse(this.state.longDescription);
            }

            return _react2.default.createElement(
                'div',
                null,
                _react2.default.createElement(
                    'div',
                    { className: 'well' },
                    _react2.default.createElement(
                        'h2',
                        null,
                        this.props.sentenceName
                    ),
                    _react2.default.createElement(
                        'p',
                        null,
                        this.props.sentenceDescription
                    ),
                    this.state.loading ? _react2.default.createElement('div', { className: 'sk-spinner sk-spinner-pulse' }) : _react2.default.createElement(
                        _reactRedux.Provider,
                        { store: this.previewStore },
                        _react2.default.createElement(_fragmentExplorer2.default, { stateInUrl: false })
                    ),
                    longDescription ? _react2.default.createElement(
                        'div',
                        { className: 'ed-fragment-long-description' },
                        longDescription
                    ) : ''
                ),
                _react2.default.createElement(
                    'nav',
                    null,
                    _react2.default.createElement(
                        'ul',
                        { className: 'pager' },
                        _react2.default.createElement(
                            'li',
                            { className: 'previous' },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: this.onPreviousClick.bind(this) },
                                '\u2190 Previous step'
                            )
                        ),
                        _react2.default.createElement(
                            'li',
                            { className: 'next' },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: this.onSubmit.bind(this) },
                                'Confirm and save \xA0 \xA0',
                                _react2.default.createElement('span', { className: 'glyphicon glyphicon-save' })
                            )
                        )
                    )
                )
            );
        }
    }]);

    return EDPreviewForm;
}(_react2.default.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        fragments: state.fragments,
        sentenceName: state.name,
        sentenceSource: state.source,
        sentenceLanguageId: state.language_id,
        sentenceDescription: state.description,
        sentenceLongDescription: state.long_description,
        sentenceIsNeologism: state.is_neologism,
        sentenceId: state.id
    };
};

exports.default = (0, _reactRouter.withRouter)((0, _reactRedux.connect)(mapStateToProps)(EDPreviewForm));

/***/ }),

/***/ 191:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _reactRedux = __webpack_require__(17);

var _reactRouter = __webpack_require__(11);

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _smoothscrollPolyfill = __webpack_require__(46);

var _admin = __webpack_require__(103);

var _edForm = __webpack_require__(55);

var _markdownEditor = __webpack_require__(45);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

var _errorList = __webpack_require__(44);

var _errorList2 = _interopRequireDefault(_errorList);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDSentenceForm = function (_EDStatefulFormCompon) {
    _inherits(EDSentenceForm, _EDStatefulFormCompon);

    function EDSentenceForm(props) {
        _classCallCheck(this, EDSentenceForm);

        var _this = _possibleConstructorReturn(this, (EDSentenceForm.__proto__ || Object.getPrototypeOf(EDSentenceForm)).call(this, props));

        _this.state = {
            name: '',
            source: '',
            language_id: 0,
            is_neologism: false,
            description: '',
            long_description: '',
            errors: undefined
        };

        (0, _smoothscrollPolyfill.polyfill)();
        return _this;
    }

    _createClass(EDSentenceForm, [{
        key: 'componentDidMount',
        value: function componentDidMount() {
            this.setState({
                id: this.props.sentenceId,
                name: this.props.sentenceName,
                source: this.props.sentenceSource,
                language_id: this.props.sentenceLanguageId,
                description: this.props.sentenceDescription,
                long_description: this.props.sentenceLongDescription,
                is_neologism: this.props.sentenceIsNeologism
            });
        }
    }, {
        key: 'onSubmit',
        value: function onSubmit(ev) {
            var _this2 = this;

            ev.preventDefault();

            var state = this.state;
            var payload = {
                id: state.id,
                name: state.name,
                source: state.source,
                language_id: state.language_id,
                description: state.description,
                long_description: state.long_description,
                is_neologism: state.is_neologism
            };

            _axios2.default.post('/admin/sentence/validate', payload).then(function (request) {
                return _this2.onValidateSuccess(request, payload);
            }, function (request) {
                return _this2.onValidateFail(request, payload);
            });
        }
    }, {
        key: 'onValidateSuccess',
        value: function onValidateSuccess(request, payload) {
            this.setState({
                errors: undefined
            });

            // Make the changes permanent (in the client) by dispatching them on to Redux.
            this.props.dispatch((0, _admin.setSentenceData)(payload));

            // Move forward to the next step
            this.props.history.goForward();
        }
    }, {
        key: 'onValidateFail',
        value: function onValidateFail(request, payload) {
            // Laravel returns 422 when the request fails validation. In the event that
            // we received an alternate status code, bail, as we do not know what that payload
            // contains.
            if (request.response.status !== EDConfig.apiValidationErrorStatusCode) {
                return;
            }

            // Laravel returns a dictionary with the name of the component as the key.
            // Flatten the errors array, by aggregating all validation errors. 
            var groupedErrors = request.response.data;
            var componentNames = Object.keys(groupedErrors);
            var aggregatedErrors = [];

            var _iteratorNormalCompletion = true;
            var _didIteratorError = false;
            var _iteratorError = undefined;

            try {
                for (var _iterator = componentNames[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                    var componentName = _step.value;

                    aggregatedErrors = [].concat(_toConsumableArray(aggregatedErrors), _toConsumableArray(groupedErrors[componentName]));
                }
            } catch (err) {
                _didIteratorError = true;
                _iteratorError = err;
            } finally {
                try {
                    if (!_iteratorNormalCompletion && _iterator.return) {
                        _iterator.return();
                    }
                } finally {
                    if (_didIteratorError) {
                        throw _iteratorError;
                    }
                }
            }

            this.setState({
                errors: aggregatedErrors
            });

            // Scroll to the top of the page in the event that the client might have
            // scrolled too far down to notice the error messages.
            window.scroll({
                top: 0,
                behavior: 'smooth'
            });
        }
    }, {
        key: 'render',
        value: function render() {
            var _this3 = this;

            return _react2.default.createElement(
                'form',
                { onSubmit: this.onSubmit.bind(this) },
                _react2.default.createElement(_errorList2.default, { errors: this.state.errors }),
                _react2.default.createElement(
                    'p',
                    null,
                    'This is the first step of three steps. Please provide some information about about your phrase. You will specify the phrase itself on the next step. Longer texts such as poetry, letters, texts, etcetera are also supported.'
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-name', className: 'control-label' },
                        'Title'
                    ),
                    _react2.default.createElement('input', { type: 'text', className: 'form-control', id: 'ed-sentence-name', name: 'name',
                        value: this.state.name, onChange: _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', this).bind(this) })
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-source', className: 'control-label' },
                        'Source'
                    ),
                    _react2.default.createElement('input', { type: 'text', className: 'form-control', id: 'ed-sentence-source', name: 'source',
                        value: this.state.source, onChange: _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', this).bind(this) })
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-language', className: 'control-label' },
                        'Language'
                    ),
                    _react2.default.createElement(
                        'select',
                        { className: 'form-control', id: 'ed-sentence-language', name: 'language_id',
                            onChange: function onChange(ev) {
                                return _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', _this3).call(_this3, ev, 'number');
                            }, value: this.state.language_id },
                        _react2.default.createElement('option', { value: '0' }),
                        this.props.languages.filter(function (l) {
                            return l.is_invented;
                        }).map(function (l) {
                            return _react2.default.createElement(
                                'option',
                                { value: l.id, key: l.id },
                                l.name
                            );
                        })
                    )
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-description', className: 'control-label' },
                        'Summary'
                    ),
                    _react2.default.createElement('textarea', { id: 'ed-sentence-description', className: 'form-control', name: 'description',
                        value: this.state.description, onChange: _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', this).bind(this) })
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'checkbox' },
                    _react2.default.createElement(
                        'label',
                        null,
                        _react2.default.createElement('input', { type: 'checkbox', checked: this.state.is_neologism, name: 'is_neologism',
                            value: true, onChange: _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', this).bind(this) }),
                        'Neologism'
                    )
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-long-description', className: 'control-label' },
                        'Description'
                    ),
                    _react2.default.createElement(_markdownEditor2.default, { componentId: 'ed-sentence-long-description', componentName: 'long_description', rows: 8,
                        value: this.state.long_description, onChange: _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', this).bind(this) })
                ),
                _react2.default.createElement(
                    'nav',
                    null,
                    _react2.default.createElement(
                        'ul',
                        { className: 'pager' },
                        _react2.default.createElement(
                            'li',
                            { className: 'next' },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: this.onSubmit.bind(this) },
                                'Next step \u2192'
                            )
                        )
                    )
                )
            );
        }
    }]);

    return EDSentenceForm;
}(_edForm.EDStatefulFormComponent);

var mapStateToProps = function mapStateToProps(state) {
    return {
        languages: state.languages,
        sentenceName: state.name,
        sentenceSource: state.source,
        sentenceLanguageId: state.language_id,
        sentenceDescription: state.description,
        sentenceLongDescription: state.long_description,
        sentenceIsNeologism: state.is_neologism,
        sentenceId: state.id
    };
};

exports.default = (0, _reactRouter.withRouter)((0, _reactRedux.connect)(mapStateToProps)(EDSentenceForm));

/***/ }),

/***/ 35:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _htmlToReact = __webpack_require__(22);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Represents a single gloss. A gloss is also called a 'translation' and is reserved for invented languages.
 */
var EDBookGloss = function (_React$Component) {
    _inherits(EDBookGloss, _React$Component);

    function EDBookGloss() {
        _classCallCheck(this, EDBookGloss);

        return _possibleConstructorReturn(this, (EDBookGloss.__proto__ || Object.getPrototypeOf(EDBookGloss)).apply(this, arguments));
    }

    _createClass(EDBookGloss, [{
        key: 'processHtml',
        value: function processHtml(html) {
            var _this2 = this;

            var definitions = new _htmlToReact.ProcessNodeDefinitions(_react2.default);
            var instructions = [
            // Special behaviour for <a> as they are reference links.
            {
                shouldProcessNode: function shouldProcessNode(node) {
                    return node.name === 'a';
                },
                processNode: function processNode(node, children) {
                    var nodeElements = definitions.processDefaultNode(node, children);
                    if (node.attribs.class !== 'ed-word-reference') {
                        return nodeElements;
                    }

                    // Replace reference links with a link that is aware of
                    // the component, and can intercept click attempts.
                    var href = node.attribs.href;
                    var title = node.attribs.title;
                    var word = node.attribs['data-word'];
                    var childElements = nodeElements.props.children;

                    return _react2.default.createElement(
                        'a',
                        { href: href,
                            onClick: function onClick(ev) {
                                return _this2.onReferenceLinkClick(ev, word);
                            },
                            title: title },
                        childElements
                    );
                }
            },
            // Default behaviour for all else.
            {
                shouldProcessNode: function shouldProcessNode(node) {
                    return true;
                },
                processNode: definitions.processDefaultNode
            }];

            var parser = new _htmlToReact.Parser();
            return parser.parseWithInstructions(html, function (n) {
                return true;
            }, instructions);
        }
    }, {
        key: 'onReferenceLinkClick',
        value: function onReferenceLinkClick(ev, word) {
            ev.preventDefault();

            if (this.props.onReferenceLinkClick) {
                this.props.onReferenceLinkClick({
                    word: word
                });
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var gloss = this.props.gloss;
            var id = 'translation-block-' + gloss.id;

            var comments = null;
            if (gloss.comments) {
                comments = this.processHtml(gloss.comments);
            }

            return _react2.default.createElement(
                'blockquote',
                { itemScope: 'itemscope', itemType: 'http://schema.org/Article', id: id, className: (0, _classnames2.default)({ 'contribution': !gloss.is_canon }) },
                _react2.default.createElement(
                    'h3',
                    { rel: 'trans-word', className: 'trans-word' },
                    !gloss.is_canon || gloss.is_uncertain || !gloss.is_latest ? _react2.default.createElement(
                        'a',
                        { href: '/about', title: 'Unverified or debatable content.' },
                        _react2.default.createElement('span', { className: 'glyphicon glyphicon-question-sign' })
                    ) : '',
                    ' ',
                    _react2.default.createElement(
                        'span',
                        { itemProp: 'headline' },
                        gloss.word
                    ),
                    gloss.external_link_format && gloss.external_id ? _react2.default.createElement(
                        'a',
                        { href: gloss.external_link_format.replace(/\{ExternalID\}/g, gloss.external_id),
                            className: 'ed-external-link-button',
                            title: 'Open on ' + gloss.translation_group_name + ' (new tab/window)',
                            target: '_blank' },
                        _react2.default.createElement('span', { className: 'glyphicon glyphicon-globe pull-right' })
                    ) : ''
                ),
                _react2.default.createElement(
                    'p',
                    null,
                    gloss.tengwar ? _react2.default.createElement(
                        'span',
                        { className: 'tengwar' },
                        gloss.tengwar
                    ) : '',
                    ' ',
                    gloss.type != 'unset' ? _react2.default.createElement(
                        'span',
                        { className: 'word-type', rel: 'trans-type' },
                        gloss.type,
                        '.'
                    ) : '',
                    ' ',
                    _react2.default.createElement(
                        'span',
                        { rel: 'trans-translation', itemProp: 'keywords' },
                        gloss.translation
                    )
                ),
                comments,
                _react2.default.createElement(
                    'footer',
                    null,
                    gloss.source ? _react2.default.createElement(
                        'span',
                        { className: 'word-source', rel: 'trans-source' },
                        '[',
                        gloss.source,
                        ']'
                    ) : '',
                    ' ',
                    gloss.Etymology ? _react2.default.createElement(
                        'span',
                        { className: 'word-etymology', rel: 'trans-etymology' },
                        gloss.etymology,
                        '.'
                    ) : '',
                    ' ',
                    gloss.translation_group_id ? _react2.default.createElement(
                        'span',
                        null,
                        'Group: ',
                        _react2.default.createElement(
                            'span',
                            { itemProp: 'sourceOrganization' },
                            gloss.translation_group_name,
                            '.'
                        )
                    ) : '',
                    ' Published: ',
                    _react2.default.createElement(
                        'span',
                        { itemProp: 'datePublished' },
                        gloss.created_at
                    ),
                    ' by ',
                    _react2.default.createElement(
                        'a',
                        { href: gloss.account_url, itemProp: 'author', rel: 'author', title: 'View profile for ' + gloss.account_name + '.' },
                        gloss.account_name
                    )
                )
            );
        }
    }]);

    return EDBookGloss;
}(_react2.default.Component);

exports.default = EDBookGloss;

/***/ }),

/***/ 36:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
var REQUEST_FRAGMENT = exports.REQUEST_FRAGMENT = 'EDSR_REQUEST_FRAGMENT';
var RECEIVE_FRAGMENT = exports.RECEIVE_FRAGMENT = 'EDSR_RECEIVE_FRAGMENT';

var EDSentenceReducer = function EDSentenceReducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
        fragments: JSON.parse(document.getElementById('ed-preload-fragments').textContent),
        fragmentId: undefined,
        bookData: undefined,
        loading: false
    };
    var action = arguments[1];

    switch (action.type) {

        case REQUEST_FRAGMENT:
            return Object.assign({}, state, {
                fragmentId: action.fragmentId,
                loading: true
            });

        case RECEIVE_FRAGMENT:
            return Object.assign({}, state, {
                translationId: action.translationId,
                bookData: action.bookData,
                loading: false
            });

        default:
            return state;
    }
};

exports.default = EDSentenceReducer;

/***/ }),

/***/ 402:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(161);


/***/ }),

/***/ 59:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.selectFragment = undefined;

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _edPromise = __webpack_require__(56);

var _reducers = __webpack_require__(36);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var selectFragment = exports.selectFragment = function selectFragment(fragmentId, translationId) {
    return function (dispatch) {
        dispatch({
            type: _reducers.REQUEST_FRAGMENT,
            fragmentId: fragmentId
        });

        var start = new Date().getTime();
        (0, _edPromise.deferredResolve)(_axios2.default.get(_edConfig2.default.api('/book/translate/' + translationId)), 800).then(function (resp) {
            dispatch({
                type: _reducers.RECEIVE_FRAGMENT,
                bookData: resp.data,
                translationId: translationId
            });
        });
    };
};

/***/ }),

/***/ 60:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _reactRedux = __webpack_require__(17);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _actions = __webpack_require__(59);

var _fragment = __webpack_require__(61);

var _fragment2 = _interopRequireDefault(_fragment);

var _tengwarFragment = __webpack_require__(62);

var _tengwarFragment2 = _interopRequireDefault(_tengwarFragment);

var _bookGloss = __webpack_require__(35);

var _bookGloss2 = _interopRequireDefault(_bookGloss);

var _htmlToReact = __webpack_require__(22);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDFragmentExplorer = function (_React$Component) {
    _inherits(EDFragmentExplorer, _React$Component);

    function EDFragmentExplorer(props) {
        _classCallCheck(this, EDFragmentExplorer);

        var _this = _possibleConstructorReturn(this, (EDFragmentExplorer.__proto__ || Object.getPrototypeOf(EDFragmentExplorer)).call(this, props));

        _this.state = {
            fragmentIndex: 0
        };
        return _this;
    }

    /**
     * Component has mounted and initial state retrieved from the server should be applied.
     */


    _createClass(EDFragmentExplorer, [{
        key: 'componentDidMount',
        value: function componentDidMount() {
            var fragmentIndex = 0;
            // Does the shebang specify the fragment ID?
            if (/^#![0-9]+$/.test(window.location.hash)) {
                var fragmentId = parseInt(String(window.location.hash).substr(2), 10);
                if (fragmentId) {
                    fragmentIndex = Math.max(this.props.fragments.findIndex(function (f) {
                        return f.id === fragmentId;
                    }), 0);
                }
            }

            // A little hack for causing the first fragment to be highlighted
            this.onNavigate({}, fragmentIndex);
        }

        /**
         * Retrieves the fragment index for the next fragment, or returns the current fragment index
         * if none exists.
         */

    }, {
        key: 'nextFragmentIndex',
        value: function nextFragmentIndex() {
            for (var i = this.state.fragmentIndex + 1; i < this.props.fragments.length; i += 1) {
                var fragment = this.props.fragments[i];

                if (!fragment.interpunctuation) {
                    return i;
                }
            }

            return this.state.fragmentIndex;
        }

        /**
         * Retrieves the fragment index for the previous fragment, or returns the current fragment index
         * if none exists.
         */

    }, {
        key: 'previousFragmentIndex',
        value: function previousFragmentIndex() {
            for (var i = this.state.fragmentIndex - 1; i > -1; i -= 1) {
                var fragment = this.props.fragments[i];

                if (!fragment.interpunctuation) {
                    return i;
                }
            }

            return this.state.fragmentIndex;
        }

        /**
         * Event handler for the onFragmentClick event.
         * 
         * @param {*} ev 
         */

    }, {
        key: 'onFragmentClick',
        value: function onFragmentClick(ev) {
            if (ev.preventDefault) {
                ev.preventDefault();
            }

            var fragmentIndex = this.props.fragments.findIndex(function (f) {
                return f.id === ev.id;
            });
            if (fragmentIndex === -1) {
                return;
            }

            this.setState({
                fragmentIndex: fragmentIndex
            });
            if (this.props.stateInUrl) {
                window.location.hash = '!' + ev.id;
            }

            this.props.dispatch((0, _actions.selectFragment)(ev.id, ev.translation_id));
        }

        /**
         * Navigates to the specified fragment index by receiving it from the array of fragments, and
         * dispatching a select fragment signal.
         * 
         * @param {*} ev 
         * @param {*} fragmentIndex 
         */

    }, {
        key: 'onNavigate',
        value: function onNavigate(ev, fragmentIndex) {
            if (ev.preventDefault) {
                ev.preventDefault();
            }

            var fragment = this.props.fragments[fragmentIndex];
            this.onFragmentClick({
                id: fragment.id,
                translation_id: fragment.translation_id
            });
        }

        /**
         * Dispatches a window message to the search result component, requesting a search.
         * @param {*} data 
         */

    }, {
        key: 'onReferenceLinkClick',
        value: function onReferenceLinkClick(data) {
            _edConfig2.default.message(_edConfig2.default.messageNavigateName, data);
        }
    }, {
        key: 'render',
        value: function render() {
            var _this2 = this;

            var section = null;
            var fragment = null;
            var parser = null;

            if (!this.props.loading && this.props.bookData && this.props.bookData.sections.length > 0) {
                // Comments may contain HTML because it's parsed by the server as markdown. The HtmlToReact parser will
                // examine the HTML and turn it into React components.
                section = this.props.bookData.sections[0];
                fragment = this.props.fragments.find(function (f) {
                    return f.id === _this2.props.fragmentId;
                });
                parser = new _htmlToReact.Parser();
            }

            var previousIndex = this.previousFragmentIndex();
            var nextIndex = this.nextFragmentIndex();

            return _react2.default.createElement(
                'div',
                { className: 'well ed-fragment-navigator' },
                _react2.default.createElement(
                    'p',
                    { className: 'tengwar ed-tengwar-fragments' },
                    this.props.fragments.map(function (f) {
                        return _react2.default.createElement(_tengwarFragment2.default, { fragment: f,
                            key: 'tng' + f.id,
                            selected: f.id === _this2.props.fragmentId });
                    })
                ),
                _react2.default.createElement(
                    'p',
                    { className: 'ed-elvish-fragments' },
                    this.props.fragments.map(function (f) {
                        return _react2.default.createElement(_fragment2.default, { fragment: f,
                            key: 'frg' + f.id,
                            selected: f.id === _this2.props.fragmentId,
                            onClick: _this2.onFragmentClick.bind(_this2) });
                    })
                ),
                _react2.default.createElement(
                    'nav',
                    null,
                    _react2.default.createElement(
                        'ul',
                        { className: 'pager' },
                        _react2.default.createElement(
                            'li',
                            { className: (0, _classnames2.default)('previous', { 'hidden': previousIndex === this.state.fragmentIndex }) },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: function onClick(ev) {
                                        return _this2.onNavigate(ev, previousIndex);
                                    } },
                                '\u2190 ',
                                this.props.fragments[previousIndex].fragment
                            )
                        ),
                        _react2.default.createElement(
                            'li',
                            { className: (0, _classnames2.default)('next', { 'hidden': nextIndex === this.state.fragmentIndex }) },
                            _react2.default.createElement(
                                'a',
                                { href: '#', onClick: function onClick(ev) {
                                        return _this2.onNavigate(ev, nextIndex);
                                    } },
                                this.props.fragments[nextIndex].fragment,
                                ' \u2192'
                            )
                        )
                    )
                ),
                this.props.loading ? _react2.default.createElement('div', { className: 'sk-spinner sk-spinner-pulse' }) : section ? _react2.default.createElement(
                    'div',
                    null,
                    fragment.grammarType ? _react2.default.createElement(
                        'div',
                        null,
                        _react2.default.createElement(
                            'em',
                            null,
                            fragment.grammarType
                        )
                    ) : '',
                    _react2.default.createElement(
                        'div',
                        null,
                        fragment.comments ? parser.parse(fragment.comments) : ''
                    ),
                    _react2.default.createElement('hr', null),
                    _react2.default.createElement(
                        'div',
                        null,
                        section.glosses.map(function (g) {
                            return _react2.default.createElement(_bookGloss2.default, { gloss: g,
                                language: section.language,
                                key: g.id,
                                onReferenceLinkClick: _this2.onReferenceLinkClick.bind(_this2) });
                        })
                    )
                ) : ''
            );
        }
    }]);

    return EDFragmentExplorer;
}(_react2.default.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        fragments: state.fragments,
        fragmentId: state.fragmentId,
        bookData: state.bookData,
        loading: state.loading
    };
};

EDFragmentExplorer.defaultProps = {
    stateInUrl: true
};

exports.default = (0, _reactRedux.connect)(mapStateToProps)(EDFragmentExplorer);

/***/ }),

/***/ 61:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDFragment = function (_React$Component) {
    _inherits(EDFragment, _React$Component);

    function EDFragment() {
        _classCallCheck(this, EDFragment);

        return _possibleConstructorReturn(this, (EDFragment.__proto__ || Object.getPrototypeOf(EDFragment)).apply(this, arguments));
    }

    _createClass(EDFragment, [{
        key: 'onFragmentClick',
        value: function onFragmentClick(ev) {
            ev.preventDefault();

            if (this.props.onClick) {
                this.props.onClick({
                    id: this.props.fragment.id,
                    url: ev.target.href,
                    translation_id: this.props.fragment.translation_id
                });
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var f = this.props.fragment;

            if (f.interpunctuation) {
                return _react2.default.createElement(
                    'span',
                    null,
                    f.fragment
                );
            }

            return _react2.default.createElement(
                'span',
                null,
                ' ',
                _react2.default.createElement(
                    'a',
                    { className: (0, _classnames2.default)({ 'active': this.props.selected }),
                        href: '/wt/' + f.translation_id,
                        onClick: this.onFragmentClick.bind(this) },
                    f.fragment
                )
            );
        }
    }]);

    return EDFragment;
}(_react2.default.Component);

exports.default = EDFragment;

/***/ }),

/***/ 62:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDTengwarFragment = function (_React$Component) {
    _inherits(EDTengwarFragment, _React$Component);

    function EDTengwarFragment() {
        _classCallCheck(this, EDTengwarFragment);

        return _possibleConstructorReturn(this, (EDTengwarFragment.__proto__ || Object.getPrototypeOf(EDTengwarFragment)).apply(this, arguments));
    }

    _createClass(EDTengwarFragment, [{
        key: 'render',
        value: function render() {
            var f = this.props.fragment;

            return _react2.default.createElement(
                'span',
                { className: (0, _classnames2.default)({ 'active': this.props.selected }) },
                (f.interpunctuation ? '' : ' ') + f.tengwar
            );
        }
    }]);

    return EDTengwarFragment;
}(_react2.default.Component);

exports.default = EDTengwarFragment;

/***/ })

},[402]);