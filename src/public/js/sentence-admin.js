webpackJsonp([2],{

/***/ 151:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(31);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _reactRouterDom = __webpack_require__(83);

var _reactRedux = __webpack_require__(18);

var _redux = __webpack_require__(32);

var _reduxThunk = __webpack_require__(40);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _admin = __webpack_require__(182);

var _admin2 = _interopRequireDefault(_admin);

var _sessionStorageState = __webpack_require__(171);

var _sentenceForm = __webpack_require__(178);

var _sentenceForm2 = _interopRequireDefault(_sentenceForm);

var _fragmentForm = __webpack_require__(177);

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
            languages: window.EDConfig.languages()
        });
    } else {
        preloadedState = (0, _sessionStorageState.loadState)('sentence');
        creating = true;
    }

    var store = (0, _redux.createStore)(_admin2.default, preloadedState, (0, _redux.applyMiddleware)(_reduxThunk2.default));

    if (creating) {
        store.subscribe(function () {
            (0, _sessionStorageState.saveState)('sentence', store.getState());
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

/***/ 171:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
var loadState = exports.loadState = function loadState(prefix) {
    try {
        var serializedState = sessionStorage.getItem(prefix + '-state');
        if (!serializedState) {
            return undefined;
        }

        return JSON.parse(serializedState);
    } catch (err) {
        return undefined;
    }
};

var saveState = exports.saveState = function saveState(prefix, state) {
    try {
        var serializedState = JSON.stringify(state);
        sessionStorage.setItem(prefix + '-state', serializedState);
    } catch (err) {
        // avoid saving state
    }
};

/***/ }),

/***/ 177:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(10);

var _classnames2 = _interopRequireDefault(_classnames);

var _reactRedux = __webpack_require__(18);

var _reactRouter = __webpack_require__(9);

var _axios = __webpack_require__(21);

var _axios2 = _interopRequireDefault(_axios);

var _smoothscrollPolyfill = __webpack_require__(41);

var _form = __webpack_require__(90);

var _markdownEditor = __webpack_require__(42);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

var _errorList = __webpack_require__(89);

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
            phrase: phrase,
            fragments: props.fragments || []
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

            var currentFragments = this.state.fragments || [];
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
                    var insertAt = i === 0 ? i : i + 1;
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
                    newFragments[_i] = existingFragment;
                }

                if (!newFragments[_i].interpunctuation) {
                    words.push(newFragments[_i].fragment);
                }
            };

            for (var _i = 0; _i < newFragments.length; _i += 1) {
                _loop(_i);
            }

            this.setState({
                fragments: newFragments
            });

            if (words.length > 0) {
                _axios2.default.post(window.EDConfig.api('/book/suggest'), {
                    words: words,
                    language_id: this.props.languageId
                });
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
                    this.state.fragments.map(function (f, i) {
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
}(_form.EDStatefulFormComponent);

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
                    { href: '#', onClick: this.onFragmentClick.bind(this) },
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
        fragments: state.fragments
    };
};

exports.default = (0, _reactRouter.withRouter)((0, _reactRedux.connect)(mapStateToProps)(EDFragmentForm));

/***/ }),

/***/ 178:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _get = function get(object, property, receiver) { if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { return get(parent, property, receiver); } } else if ("value" in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } };

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _classnames = __webpack_require__(10);

var _classnames2 = _interopRequireDefault(_classnames);

var _reactRedux = __webpack_require__(18);

var _reactRouter = __webpack_require__(9);

var _axios = __webpack_require__(21);

var _axios2 = _interopRequireDefault(_axios);

var _smoothscrollPolyfill = __webpack_require__(41);

var _form = __webpack_require__(90);

var _markdownEditor = __webpack_require__(42);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

var _errorList = __webpack_require__(89);

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
}(_form.EDStatefulFormComponent);

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

/***/ 182:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
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
        languages: window.EDConfig.languages()
    };
    var action = arguments[1];

    switch (action.type) {
        case SET_FRAGMENTS:

            break;
        case SET_FRAGMENT_DATA:

            break;
        default:
            return state;
    }
};

exports.default = EDSentenceAdminReducer;

/***/ }),

/***/ 382:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(151);


/***/ }),

/***/ 40:
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

/***/ }),

/***/ 42:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _axios = __webpack_require__(21);

var _axios2 = _interopRequireDefault(_axios);

var _classnames = __webpack_require__(10);

var _classnames2 = _interopRequireDefault(_classnames);

var _htmlToReact = __webpack_require__(27);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var MDMarkdownEditTab = 0;
var MDMarkdownPreviewTab = 1;

var EDMarkdownEditor = function (_React$Component) {
    _inherits(EDMarkdownEditor, _React$Component);

    function EDMarkdownEditor(props) {
        _classCallCheck(this, EDMarkdownEditor);

        var _this = _possibleConstructorReturn(this, (EDMarkdownEditor.__proto__ || Object.getPrototypeOf(EDMarkdownEditor)).call(this, props));

        _this.state = {
            value: _this.props.value || '',
            currentTab: MDMarkdownEditTab
        };
        return _this;
    }

    _createClass(EDMarkdownEditor, [{
        key: 'applyHtml',
        value: function applyHtml(resp) {
            this.setState({
                html: resp.data.html
            });
        }
    }, {
        key: 'onOpenTab',
        value: function onOpenTab(ev, tab) {
            ev.preventDefault();

            // Is the tab currently opened?
            if (this.state.currentTab === tab) {
                return;
            }

            // Let the server render the Markdown code
            if (tab === MDMarkdownPreviewTab) {
                if (/^\s*$/.test(this.state.value)) {
                    return;
                }

                // Apply dimensions to the markup container to avoid pushing the client
                // up a notch while switching tabs.
                var boundingRect = this.textArea.getBoundingClientRect();
                this.markupContainer.style.minHeight = boundingRect.height + 'px';

                // Let the server parse the markdown
                _axios2.default.post(window.EDConfig.api('/utility/markdown'), { markdown: this.state.value }).then(this.applyHtml.bind(this));
            }

            this.setState({
                html: null,
                currentTab: tab
            });
        }
    }, {
        key: 'onValueChange',
        value: function onValueChange(ev) {
            var _this2 = this;

            this.setState({
                value: ev.target.value
            });

            if (typeof this.props.onChange === 'function') {
                // Remove the synthetic event from the pool and allow references to the event to be retained by user code. 
                // See https://facebook.github.io/react/docs/events.html
                ev.persist();
                window.setTimeout(function () {
                    return _this2.props.onChange(ev);
                }, 0);
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var _this3 = this;

            var html = null;

            if (this.state.currentTab === MDMarkdownPreviewTab && this.state.html) {
                var parser = new _htmlToReact.Parser();
                html = parser.parse(this.state.html);
            }

            return _react2.default.createElement(
                'div',
                { className: 'clearfix' },
                _react2.default.createElement(
                    'ul',
                    { className: 'nav nav-tabs' },
                    _react2.default.createElement(
                        'li',
                        { role: 'presentation',
                            className: (0, _classnames2.default)({ 'active': this.state.currentTab === MDMarkdownEditTab }) },
                        _react2.default.createElement(
                            'a',
                            { href: '#', onClick: function onClick(e) {
                                    return _this3.onOpenTab(e, MDMarkdownEditTab);
                                } },
                            'Edit'
                        )
                    ),
                    _react2.default.createElement(
                        'li',
                        { role: 'presentation',
                            className: (0, _classnames2.default)({
                                'active': this.state.currentTab === MDMarkdownPreviewTab,
                                'disabled': !this.state.value
                            }) },
                        _react2.default.createElement(
                            'a',
                            { href: '#', onClick: function onClick(e) {
                                    return _this3.onOpenTab(e, MDMarkdownPreviewTab);
                                } },
                            'Preview'
                        )
                    )
                ),
                _react2.default.createElement(
                    'div',
                    { className: (0, _classnames2.default)({ 'hidden': this.state.currentTab !== MDMarkdownEditTab }) },
                    _react2.default.createElement('textarea', { className: 'form-control',
                        name: this.props.componentName,
                        id: this.props.componentId,
                        rows: this.props.rows,
                        value: this.state.value,
                        onChange: this.onValueChange.bind(this),
                        ref: function ref(textarea) {
                            return _this3.textArea = textarea;
                        } }),
                    _react2.default.createElement(
                        'small',
                        { className: 'pull-right' },
                        ' Supports Markdown. ',
                        _react2.default.createElement(
                            'a',
                            { href: 'https://en.wikipedia.org/wiki/Markdown', target: '_blank' },
                            'Read more (opens a new window)'
                        ),
                        '.'
                    )
                ),
                _react2.default.createElement(
                    'div',
                    { className: (0, _classnames2.default)({ 'hidden': this.state.currentTab !== MDMarkdownPreviewTab }),
                        ref: function ref(container) {
                            return _this3.markupContainer = container;
                        } },
                    html ? html : _react2.default.createElement(
                        'p',
                        null,
                        'Interpreting ...'
                    )
                )
            );
        }
    }]);

    return EDMarkdownEditor;
}(_react2.default.Component);

EDMarkdownEditor.defaultProps = {
    rows: 15,
    componentName: 'markdownBody'
};

exports.default = EDMarkdownEditor;

/***/ }),

/***/ 89:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var EDErrorList = function EDErrorList(props) {
    var errors = props.errors;

    if (!Array.isArray(errors)) {
        return _react2.default.createElement("div", { className: "zero-errors" });
    }

    return _react2.default.createElement(
        "div",
        { className: "alert alert-danger" },
        _react2.default.createElement(
            "ul",
            null,
            errors.map(function (error, i) {
                return _react2.default.createElement(
                    "li",
                    { key: i },
                    error
                );
            })
        )
    );
};

exports.default = EDErrorList;

/***/ }),

/***/ 90:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.EDStatefulFormComponent = undefined;

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Provides functionality to sync form components with component state.
 */
var EDStatefulFormComponent = exports.EDStatefulFormComponent = function (_React$Component) {
    _inherits(EDStatefulFormComponent, _React$Component);

    function EDStatefulFormComponent() {
        _classCallCheck(this, EDStatefulFormComponent);

        return _possibleConstructorReturn(this, (EDStatefulFormComponent.__proto__ || Object.getPrototypeOf(EDStatefulFormComponent)).apply(this, arguments));
    }

    _createClass(EDStatefulFormComponent, [{
        key: 'onChange',


        /**
         * Handles form components' onChange-event.
         * @param {Event} ev 
         * @param {number|date|undefined} dataType 
         */
        value: function onChange(ev, dataType) {
            var target = ev.target;
            var name = target.name;
            var type = target.nodeName.toUpperCase();

            var value = undefined;

            if (type === 'INPUT') {
                switch (target.type.toUpperCase()) {
                    case 'CHECKBOX':
                    case 'RADIO':
                        value = target.checked ? value || true : undefined;
                        break;
                    case 'NUMBER':
                    case 'RANGE':
                        value = parseInt(target.value, 10);
                        break;
                    default:
                        value = target.value;
                }
            } else if (type === 'SELECT') {
                value = target.options[target.selectedIndex].value;
            } else {
                value = target.value;
            }

            if (value === undefined) {
                return;
            }

            if (dataType !== undefined) {
                switch (dataType) {
                    case 'number':
                        value = parseInt(value, 10);
                        break;
                    case 'date':
                        value = Date.parse(value);
                        break;
                }
            }

            this.setState(_defineProperty({}, name, value));
        }
    }]);

    return EDStatefulFormComponent;
}(_react2.default.Component);

/***/ })

},[382]);