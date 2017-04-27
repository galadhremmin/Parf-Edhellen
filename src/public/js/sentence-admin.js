webpackJsonp([3,5],{

/***/ 114:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
var REQUEST_SUGGESTIONS = exports.REQUEST_SUGGESTIONS = 'ED_REQUEST_SUGGESTIONS';
var RECEIVE_SUGGESTIONS = exports.RECEIVE_SUGGESTIONS = 'ED_RECEIVE_SUGGESTIONS';
var SET_FRAGMENTS = exports.SET_FRAGMENTS = 'ED_SET_FRAGMENTS';
var SET_FRAGMENT_DATA = exports.SET_FRAGMENT_DATA = 'ED_SET_FRAGMENT_DATA';

var EDSentenceAdminReducer = function EDSentenceAdminReducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
        name: '',
        source: '',
        language_id: undefined,
        description: '',
        long_description: '',
        fragments: [],
        id: 0,
        languages: window.EDConfig.languages(),
        loading: false,
        suggestions: undefined
    };
    var action = arguments[1];

    switch (action.type) {
        case REQUEST_SUGGESTIONS:
            return Object.assign({}, state, {
                loading: true
            });
            break;

        case RECEIVE_SUGGESTIONS:
            return Object.assign({}, state, {
                suggestions: action.suggestions,
                loading: false
            });
            break;

        case SET_FRAGMENTS:
            return Object.assign({}, state, {
                fragments: action.fragments
            });
            break;

        case SET_FRAGMENT_DATA:

            break;
        default:
            return state;
    }
};

exports.default = EDSentenceAdminReducer;

/***/ }),

/***/ 179:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(36);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _reactRouterDom = __webpack_require__(106);

var _reactRedux = __webpack_require__(20);

var _redux = __webpack_require__(37);

var _reduxThunk = __webpack_require__(49);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _edConfig = __webpack_require__(15);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _admin = __webpack_require__(114);

var _admin2 = _interopRequireDefault(_admin);

var _edSessionStorageState = __webpack_require__(105);

var _sentenceForm = __webpack_require__(206);

var _sentenceForm2 = _interopRequireDefault(_sentenceForm);

var _fragmentForm = __webpack_require__(205);

var _fragmentForm2 = _interopRequireDefault(_fragmentForm);

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
            (0, _edSessionStorageState.saveState)('sentence', store.getState());
        });
    }

    _reactDom2.default.render(_react2.default.createElement(
        _reactRedux.Provider,
        { store: store },
        _react2.default.createElement(
            _reactRouterDom.MemoryRouter,
            { initialEntries: ['/form', '/fragments'], initialIndex: 0 },
            _react2.default.createElement(
                'div',
                null,
                _react2.default.createElement(_reactRouterDom.Route, { path: '/form', component: _sentenceForm2.default }),
                _react2.default.createElement(_reactRouterDom.Route, { path: '/fragments', component: _fragmentForm2.default })
            )
        )
    ), document.getElementById('ed-sentence-form'));
});

/***/ }),

/***/ 203:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.setFragmentData = exports.setFragments = exports.requestSuggestions = undefined;

var _axios = __webpack_require__(31);

var _axios2 = _interopRequireDefault(_axios);

var _edConfig = __webpack_require__(15);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _edPromise = __webpack_require__(62);

var _admin = __webpack_require__(114);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var requestSuggestions = exports.requestSuggestions = function requestSuggestions(words, language_id) {
    return function (dispatch) {
        dispatch({
            type: _admin.REQUEST_SUGGESTIONS
        });

        (0, _edPromise.deferredResolve)(_axios2.default.post(_edConfig2.default.api('/book/suggest'), {
            words: words,
            language_id: language_id
        }), 800).then(function (resp) {
            dispatch({
                type: _admin.RECEIVE_SUGGESTIONS,
                suggestions: resp.data
            });
        });
    };
};

var setFragments = exports.setFragments = function setFragments(fragments) {
    return {
        type: _admin.SET_FRAGMENTS,
        fragments: fragments
    };
};

var setFragmentData = exports.setFragmentData = function setFragmentData(fragment) {};

/***/ }),

/***/ 205:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(11);

var _classnames2 = _interopRequireDefault(_classnames);

var _reactRedux = __webpack_require__(20);

var _reactRouter = __webpack_require__(8);

var _smoothscrollPolyfill = __webpack_require__(51);

var _admin = __webpack_require__(203);

var _edForm = __webpack_require__(61);

var _markdownEditor = __webpack_require__(50);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

var _errorList = __webpack_require__(60);

var _errorList2 = _interopRequireDefault(_errorList);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

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
            phrase: phrase
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
        key: 'onPreviousClick',
        value: function onPreviousClick(ev) {
            ev.preventDefault();
            this.props.history.goBack();
        }
    }, {
        key: 'onPhraseChange',
        value: function onPhraseChange(ev) {
            var _this2 = this;

            ev.preventDefault();

            var currentFragments = this.props.fragments || [];
            var newFragments = this.state.phrase.replace(/\r\n/g, "\n").split(' ').map(function (f) {
                return _this2.createFragment(f);
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
                        newFragments.splice(i, 1);

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

            this.props.dispatch((0, _admin.setFragments)(newFragments));

            if (words.length > 0) {
                this.props.dispatch((0, _admin.requestSuggestions)(words, this.props.languageId));
            }
        }
    }, {
        key: 'onFragmentClick',
        value: function onFragmentClick(data) {}
    }, {
        key: 'onSubmit',
        value: function onSubmit() {}
    }, {
        key: 'render',
        value: function render() {
            var _this3 = this;

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
                this.props.loading ? _react2.default.createElement(
                    'div',
                    null,
                    _react2.default.createElement('div', { className: 'sk-spinner sk-spinner-pulse' }),
                    _react2.default.createElement(
                        'p',
                        { className: 'text-center' },
                        _react2.default.createElement(
                            'em',
                            null,
                            'Loading suggestions ...'
                        )
                    )
                ) : _react2.default.createElement(
                    'p',
                    null,
                    this.props.fragments.map(function (f, i) {
                        return _react2.default.createElement(EDFragment, { key: i, fragment: f, onClick: _this3.onFragmentClick.bind(_this3) });
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
            var _this5 = this;

            ev.preventDefault();

            if (this.props.onClick) {
                window.setTimeout(function () {
                    return _this5.props.onClick(_this5.props.fragment);
                }, 0);
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var data = this.props.fragment;

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
                        className: (0, _classnames2.default)('label', 'ed-sentence-fragment', { 'label-success': !!data.translation_id, 'label-danger': !data.translation_id }) },
                    data.fragment
                )
            );
        }
    }]);

    return EDFragment;
}(_react2.default.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        languages: state.languages,
        languageId: state.language_id,
        fragments: state.fragments,
        loading: state.loading
    };
};

exports.default = (0, _reactRouter.withRouter)((0, _reactRedux.connect)(mapStateToProps)(EDFragmentForm));

/***/ }),

/***/ 206:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(11);

var _classnames2 = _interopRequireDefault(_classnames);

var _reactRedux = __webpack_require__(20);

var _reactRouter = __webpack_require__(8);

var _axios = __webpack_require__(31);

var _axios2 = _interopRequireDefault(_axios);

var _smoothscrollPolyfill = __webpack_require__(51);

var _edForm = __webpack_require__(61);

var _markdownEditor = __webpack_require__(50);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

var _errorList = __webpack_require__(60);

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
                long_description: this.props.sentenceLongDescription
            });
        }
    }, {
        key: 'onSubmit',
        value: function onSubmit(ev) {
            ev.preventDefault();

            var state = this.state;
            var payload = {
                id: state.id,
                name: state.name,
                source: state.source,
                language_id: state.language_id,
                description: state.description,
                long_description: state.long_description
            };

            _axios2.default.post('/admin/sentence/validate', payload).then(this.onValidateSuccess.bind(this), this.onValidateFail.bind(this));
        }
    }, {
        key: 'onValidateSuccess',
        value: function onValidateSuccess() {
            this.setState({
                errors: undefined
            });

            // Move forward to the next step
            this.props.history.goForward();
        }
    }, {
        key: 'onValidateFail',
        value: function onValidateFail(request) {
            // Laravel returns 422 when the request fails validation. In the event that
            // we received an alternate status code, bail, as we do not know what that payload
            // contains.
            if (request.response.status !== 422) {
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
            var _this2 = this;

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
                                return _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', _this2).call(_this2, ev, 'number');
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
                    { className: 'form-group' },
                    _react2.default.createElement(
                        'label',
                        { htmlFor: 'ed-sentence-long-description', className: 'control-label' },
                        'Description'
                    ),
                    _react2.default.createElement(_markdownEditor2.default, { componentId: 'ed-sentence-long-description', componentName: 'long_description',
                        value: this.state.longDescription, onChange: _get(EDSentenceForm.prototype.__proto__ || Object.getPrototypeOf(EDSentenceForm.prototype), 'onChange', this).bind(this) })
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
        sentenceId: state.id
    };
};

exports.default = (0, _reactRouter.withRouter)((0, _reactRedux.connect)(mapStateToProps)(EDSentenceForm));

/***/ }),

/***/ 432:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(179);


/***/ }),

/***/ 49:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
function createThunkMiddleware(extraArgument) {
  return function (_ref) {
    var dispatch = _ref.dispatch,
        getState = _ref.getState;
    return function (next) {
      return function (action) {
        if (typeof action === 'function') {
          return action(dispatch, getState, extraArgument);
        }

        return next(action);
      };
    };
  };
}

var thunk = createThunkMiddleware();
thunk.withExtraArgument = createThunkMiddleware;

exports['default'] = thunk;

/***/ })

},[432]);