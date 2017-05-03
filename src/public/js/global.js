webpackJsonp([2],{

/***/ 101:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.requestResults = requestResults;
exports.receiveResults = receiveResults;
exports.requestNavigation = requestNavigation;
exports.receiveNavigation = receiveNavigation;
exports.advanceSelection = advanceSelection;
exports.setSelection = setSelection;
exports.fetchResults = fetchResults;
exports.beginNavigation = beginNavigation;

var _axios = __webpack_require__(16);

var _axios2 = _interopRequireDefault(_axios);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _reducers = __webpack_require__(102);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function requestResults(wordSearch) {
    return {
        type: _reducers.REQUEST_RESULTS,
        wordSearch: wordSearch
    };
}

function receiveResults(results) {
    return {
        type: _reducers.RECEIVE_RESULTS,
        items: results
    };
}

function requestNavigation(word, normalizedWord, index) {
    return {
        type: _reducers.REQUEST_NAVIGATION,
        word: word,
        normalizedWord: normalizedWord,
        index: index
    };
}

function receiveNavigation(bookData) {
    return {
        type: _reducers.RECEIVE_NAVIGATION,
        bookData: bookData
    };
}

function advanceSelection(direction) {
    return {
        type: _reducers.ADVANCE_SELECTION,
        direction: direction > 0 ? 1 : -1
    };
}

function setSelection(index) {
    return {
        type: _reducers.SET_SELECTION,
        index: index
    };
}

function fetchResults(word) {
    var reversed = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var languageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;

    if (!word || /^\s$/.test(word)) {
        return;
    }

    return function (dispatch) {
        dispatch(requestResults(word));
        _axios2.default.post(_edConfig2.default.api('/book/find'), {
            word: word,
            reversed: reversed,
            language_id: languageId
        }).then(function (resp) {
            var results = resp.data.map(function (r) {
                return {
                    word: r.k,
                    normalizedWord: r.nk
                };
            });

            dispatch(receiveResults(results));
        });
    };
}

function beginNavigation(word, normalizedWord, index, modifyState) {
    if (modifyState === undefined) {
        modifyState = true;
    }

    if (!index && index !== 0) {
        index = undefined;
    }

    var uriEncodedWord = encodeURIComponent(normalizedWord || word);
    var apiAddress = _edConfig2.default.api('/book/translate');
    var address = '/w/' + uriEncodedWord;
    var title = word + ' - Parf Edhellen';

    // When navigating using the browser's back and forward buttons,
    // the state needn't be modified.
    if (modifyState) {
        window.history.pushState(null, title, address);
    }

    // because most browsers doesn't change the document title when pushing state
    document.title = title;

    return function (dispatch) {
        dispatch(requestNavigation(word, normalizedWord || undefined, index));

        _axios2.default.post(apiAddress, { word: normalizedWord || word }).then(function (resp) {
            dispatch(receiveNavigation(resp.data));

            // Find elements which is requested to be deleted upon receiving the navigation commmand
            var elementsToDelete = document.querySelectorAll('.ed-remove-when-navigating');
            if (elementsToDelete.length > 0) {
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = elementsToDelete[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var element = _step.value;

                        element.parentNode.removeChild(element);
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
            }
        });
    };
}

/***/ }),

/***/ 102:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
var REQUEST_RESULTS = exports.REQUEST_RESULTS = 'EDSR_REQUEST_RESULTS';
var REQUEST_NAVIGATION = exports.REQUEST_NAVIGATION = 'EDSR_REQUEST_NAVIGATION';
var RECEIVE_RESULTS = exports.RECEIVE_RESULTS = 'EDSR_RECEIVE_RESULTS';
var RECEIVE_NAVIGATION = exports.RECEIVE_NAVIGATION = 'EDSR_RECEIVE_NAVIGATION';
var ADVANCE_SELECTION = exports.ADVANCE_SELECTION = 'EDSR_ADVANCE_SELECTION';
var SET_SELECTION = exports.SET_SELECTION = 'EDSR_SET_SELECTION';

var EDSearchResultsReducer = exports.EDSearchResultsReducer = function EDSearchResultsReducer() {
    var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
        loading: false,
        items: undefined,
        itemIndex: -1,
        word: undefined,
        wordSearch: undefined,
        normalizedWord: undefined,
        bookData: undefined
    };
    var action = arguments[1];

    switch (action.type) {

        case REQUEST_RESULTS:
            return Object.assign({}, state, {
                loading: true,
                wordSearch: action.wordSearch
            });

        case REQUEST_NAVIGATION:
            // perform an index check -- if the action does not specify
            // an index within the current result set, reset the result set
            // as we can assume that the client has navigated somewhere else
            // (to an entirely different word)
            var index = action.index === undefined ? -1 : action.index;
            var items = index > -1 ? state.items : undefined;

            return Object.assign({}, state, {
                loading: true,
                word: action.word,
                normalizedWord: action.normalizedWord,
                itemIndex: index,
                items: items
            });

        case RECEIVE_RESULTS:
            return Object.assign({}, state, {
                items: action.items,
                loading: false,
                itemIndex: -1
            });

        case RECEIVE_NAVIGATION:
            return Object.assign({}, state, {
                bookData: action.bookData,
                loading: false
            });

        case ADVANCE_SELECTION:
            return Object.assign({}, state, {
                itemIndex: action.direction < 0 ? state.itemIndex < 1 ? state.items.length - 1 : state.itemIndex - 1 : state.itemIndex + 1 === state.items.length ? 0 : state.itemIndex + 1
            });

        case SET_SELECTION:
            return Object.assign({}, state, {
                itemIndex: state.index === -1 ? -1 : Math.max(0, Math.min(state.items.length - 1, action.index))
            });

        default:
            return state;
    }
};

/***/ }),

/***/ 159:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


(function () {
    var onButtonClick = function onButtonClick(ev) {
        ev.preventDefault();

        var targets = document.querySelectorAll(ev.target.dataset.target);
        var className = ev.target.dataset.toggle;
        var _iteratorNormalCompletion = true;
        var _didIteratorError = false;
        var _iteratorError = undefined;

        try {
            for (var _iterator = targets[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                var target = _step.value;

                if (target.classList.contains(className)) {
                    target.classList.remove(className);
                } else {
                    target.classList.add(className);
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
    };

    var buttons = document.querySelectorAll('.navbar-toggle');
    var _iteratorNormalCompletion2 = true;
    var _didIteratorError2 = false;
    var _iteratorError2 = undefined;

    try {
        for (var _iterator2 = buttons[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
            var button = _step2.value;

            button.addEventListener('click', function (ev) {
                return onButtonClick(ev);
            });
        }
    } catch (err) {
        _didIteratorError2 = true;
        _iteratorError2 = err;
    } finally {
        try {
            if (!_iteratorNormalCompletion2 && _iterator2.return) {
                _iterator2.return();
            }
        } finally {
            if (_didIteratorError2) {
                throw _iteratorError2;
            }
        }
    }
})();

/***/ }),

/***/ 160:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(33);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _reactRedux = __webpack_require__(17);

var _redux = __webpack_require__(29);

var _reduxThunk = __webpack_require__(34);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _smoothscrollPolyfill = __webpack_require__(46);

var _reducers = __webpack_require__(102);

var _searchBar = __webpack_require__(186);

var _searchBar2 = _interopRequireDefault(_searchBar);

var _searchResults = __webpack_require__(188);

var _searchResults2 = _interopRequireDefault(_searchResults);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var store = (0, _redux.createStore)(_reducers.EDSearchResultsReducer, undefined /* <- preloaded state */
, (0, _redux.applyMiddleware)(_reduxThunk2.default));

window.addEventListener('load', function () {
    (0, _smoothscrollPolyfill.polyfill)();

    _reactDom2.default.render(_react2.default.createElement(
        _reactRedux.Provider,
        { store: store },
        _react2.default.createElement(
            'div',
            null,
            _react2.default.createElement(_searchBar2.default, null),
            _react2.default.createElement(_searchResults2.default, null)
        )
    ), document.getElementById('ed-search-component'));
});

/***/ }),

/***/ 163:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 185:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _bookGloss = __webpack_require__(35);

var _bookGloss2 = _interopRequireDefault(_bookGloss);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Represents a single section of the book. A section is usually dedicated to a language.
 */
var EDBookSection = function (_React$Component) {
    _inherits(EDBookSection, _React$Component);

    function EDBookSection() {
        _classCallCheck(this, EDBookSection);

        return _possibleConstructorReturn(this, (EDBookSection.__proto__ || Object.getPrototypeOf(EDBookSection)).apply(this, arguments));
    }

    _createClass(EDBookSection, [{
        key: 'onReferenceLinkClick',
        value: function onReferenceLinkClick(ev) {
            if (this.props.onReferenceLinkClick) {
                this.props.onReferenceLinkClick(ev);
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var _this2 = this;

            var className = 'col-sm-' + this.props.columnsMax + ' col-md-' + this.props.columnsMid + ' col-lg-' + this.props.columnsMin;
            var language = this.props.section.language;

            return _react2.default.createElement(
                'article',
                { className: className },
                _react2.default.createElement(
                    'header',
                    null,
                    _react2.default.createElement(
                        'h2',
                        { rel: 'language-box' },
                        language.name,
                        '\xA0',
                        _react2.default.createElement(
                            'span',
                            { className: 'tengwar' },
                            language.tengwar
                        )
                    )
                ),
                _react2.default.createElement(
                    'section',
                    { className: 'language-box', id: 'language-box-' + language.id },
                    this.props.section.glosses.map(function (g) {
                        return _react2.default.createElement(_bookGloss2.default, { gloss: g,
                            language: language,
                            key: g.id,
                            onReferenceLinkClick: _this2.onReferenceLinkClick.bind(_this2) });
                    })
                )
            );
        }
    }]);

    return EDBookSection;
}(_react2.default.Component);

exports.default = EDBookSection;

/***/ }),

/***/ 186:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _reactRedux = __webpack_require__(17);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _actions = __webpack_require__(101);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var EDSearchBar = function (_React$Component) {
    _inherits(EDSearchBar, _React$Component);

    function EDSearchBar(props) {
        _classCallCheck(this, EDSearchBar);

        var _this = _possibleConstructorReturn(this, (EDSearchBar.__proto__ || Object.getPrototypeOf(EDSearchBar)).call(this, props));

        _this.state = {
            isReversed: false,
            word: '',
            languageId: 0
        };
        _this.throttle = 0;
        return _this;
    }

    _createClass(EDSearchBar, [{
        key: 'componentDidMount',
        value: function componentDidMount() {
            var languageNode = document.getElementById('ed-preloaded-languages');
            this.setState({
                languages: [{
                    id: 0,
                    name: 'All languages'
                }].concat(_toConsumableArray(_edConfig2.default.languages()))
            });
        }
    }, {
        key: 'searchKeyDown',
        value: function searchKeyDown(ev) {
            var direction = ev.which === 40 ? 1 : ev.which === 38 ? -1 : undefined;

            if (direction !== undefined) {
                ev.preventDefault();
                this.props.dispatch((0, _actions.advanceSelection)(direction));
            }
        }
    }, {
        key: 'wordChange',
        value: function wordChange(ev) {
            var word = ev.target.value;
            this.setState({
                word: word
            });

            this.search(word);
        }
    }, {
        key: 'reverseChange',
        value: function reverseChange(ev) {
            this.setState({
                isReversed: ev.target.checked
            });

            this.search();
        }
    }, {
        key: 'languageChange',
        value: function languageChange(ev) {
            this.setState({
                languageId: parseInt(ev.target.value, /* radix: */10)
            });

            this.search();
        }
    }, {
        key: 'search',
        value: function search(word) {
            var _this2 = this;

            if (word === undefined) {
                word = this.state.word;
            }

            if (/^\s*$/.test(word)) {
                return; // empty search result
            }

            if (this.throttle) {
                window.clearTimeout(this.throttle);
            }

            this.throttle = window.setTimeout(function () {
                _this2.props.dispatch((0, _actions.fetchResults)(word, _this2.state.isReversed, _this2.state.languageId));
                _this2.throttle = 0;
            }, 500);
        }
    }, {
        key: 'navigate',
        value: function navigate(ev) {
            // Override default behaviour
            ev.preventDefault();

            // Dispatch a navigation request
            if (!this.props.loading) {
                this.props.dispatch((0, _actions.setSelection)(0));
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var fieldClasses = (0, _classnames2.default)('form-control', { 'disabled': this.props.loading });
            var statusClasses = (0, _classnames2.default)('glyphicon', this.props.loading ? 'glyphicon-refresh loading' : 'glyphicon-search');

            return _react2.default.createElement(
                'form',
                { onSubmit: this.navigate.bind(this) },
                _react2.default.createElement(
                    'div',
                    { className: 'row' },
                    _react2.default.createElement(
                        'div',
                        { className: 'col-md-12' },
                        _react2.default.createElement(
                            'div',
                            { className: 'input-group input-group-lg' },
                            _react2.default.createElement(
                                'span',
                                { className: 'input-group-addon' },
                                _react2.default.createElement(
                                    'span',
                                    { className: statusClasses },
                                    ' '
                                )
                            ),
                            _react2.default.createElement('input', { type: 'search', className: fieldClasses,
                                placeholder: 'What are you looking for?',
                                tabIndex: '1',
                                name: 'word',
                                autoComplete: 'off',
                                autoCapitalize: 'off',
                                autoFocus: 'true',
                                role: 'presentation',
                                value: this.state.word,
                                onKeyDown: this.searchKeyDown.bind(this),
                                onChange: this.wordChange.bind(this) })
                        )
                    )
                ),
                _react2.default.createElement(
                    'div',
                    { className: 'row' },
                    this.state.languages ? _react2.default.createElement(
                        'select',
                        { className: 'search-language-select', onChange: this.languageChange.bind(this) },
                        this.state.languages.filter(function (l) {
                            return !l.id;
                        }).map(function (l) {
                            return _react2.default.createElement(
                                'option',
                                { value: l.id, key: l.id },
                                l.name
                            );
                        }),
                        _react2.default.createElement(
                            'optgroup',
                            { label: 'Fictional languages' },
                            this.state.languages.filter(function (l) {
                                return l.is_invented && l.id;
                            }).map(function (l) {
                                return _react2.default.createElement(
                                    'option',
                                    { value: l.id, key: l.id },
                                    l.name
                                );
                            })
                        ),
                        _react2.default.createElement(
                            'optgroup',
                            { label: 'Real-world languages' },
                            this.state.languages.filter(function (l) {
                                return !l.is_invented && l.id;
                            }).map(function (l) {
                                return _react2.default.createElement(
                                    'option',
                                    { value: l.id, key: l.id },
                                    l.name
                                );
                            })
                        )
                    ) : '',
                    _react2.default.createElement(
                        'div',
                        { className: 'checkbox input-sm search-reverse-box-wrapper' },
                        _react2.default.createElement(
                            'label',
                            null,
                            _react2.default.createElement('input', { type: 'checkbox', name: 'isReversed',
                                checked: this.state.isReversed,
                                onChange: this.reverseChange.bind(this) }),
                            ' Reversed'
                        )
                    )
                )
            );
        }
    }]);

    return EDSearchBar;
}(_react2.default.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        loading: state.loading
    };
};

exports.default = (0, _reactRedux.connect)(mapStateToProps)(EDSearchBar);

/***/ }),

/***/ 187:
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

/**
 * Represents a single search result item.
 */
var EDSearchItem = function (_React$Component) {
    _inherits(EDSearchItem, _React$Component);

    function EDSearchItem() {
        _classCallCheck(this, EDSearchItem);

        return _possibleConstructorReturn(this, (EDSearchItem.__proto__ || Object.getPrototypeOf(EDSearchItem)).apply(this, arguments));
    }

    _createClass(EDSearchItem, [{
        key: 'navigate',
        value: function navigate(ev) {
            ev.preventDefault();
            this.props.onNavigate(this.props.index, this.props.item.word, this.props.item.normalizedWord);
        }
    }, {
        key: 'render',
        value: function render() {
            var cssClass = (0, _classnames2.default)({ 'selected': this.props.active });
            return _react2.default.createElement(
                'li',
                null,
                _react2.default.createElement(
                    'a',
                    { href: '#', className: cssClass, onClick: this.navigate.bind(this) },
                    this.props.item.word
                )
            );
        }
    }]);

    return EDSearchItem;
}(_react2.default.Component);

exports.default = EDSearchItem;

/***/ }),

/***/ 188:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _react = __webpack_require__(1);

var _react2 = _interopRequireDefault(_react);

var _reactRedux = __webpack_require__(17);

var _actions = __webpack_require__(101);

var _classnames = __webpack_require__(6);

var _classnames2 = _interopRequireDefault(_classnames);

var _edConfig = __webpack_require__(10);

var _edConfig2 = _interopRequireDefault(_edConfig);

var _searchItem = __webpack_require__(187);

var _searchItem2 = _interopRequireDefault(_searchItem);

var _bookSection = __webpack_require__(185);

var _bookSection2 = _interopRequireDefault(_bookSection);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

/**
 * Represents a collection of search results.
 */
var EDSearchResults = function (_React$Component) {
    _inherits(EDSearchResults, _React$Component);

    function EDSearchResults() {
        _classCallCheck(this, EDSearchResults);

        var _this = _possibleConstructorReturn(this, (EDSearchResults.__proto__ || Object.getPrototypeOf(EDSearchResults)).call(this));

        _this.state = {
            itemsOpened: true
        };

        _this.popStateHandler = _this.onPopState.bind(_this);
        _this.messageHandler = _this.onWindowMessage.bind(_this);
        return _this;
    }

    _createClass(EDSearchResults, [{
        key: 'componentWillMount',
        value: function componentWillMount() {
            window.addEventListener('popstate', this.popStateHandler);
            window.addEventListener('message', this.messageHandler, false);
        }
    }, {
        key: 'componentWillUnmount',
        value: function componentWillUnmount() {
            window.removeEventListener(this.popStateHandler);
            window.removeEventListener(this.messageHandler);
        }

        /**
         * Active index has changed?
         * @param props
         */

    }, {
        key: 'componentWillReceiveProps',
        value: function componentWillReceiveProps(props) {
            if (props.activeIndex === undefined || props.activeIndex < 0) {
                return;
            }

            var item = props.items[props.activeIndex];
            if (item.word === this.loadedWord) {
                return;
            }

            this.loadedWord = item.word;
            props.dispatch((0, _actions.beginNavigation)(item.word, item.normalizedWord, props.activeIndex));
        }
    }, {
        key: 'navigate',
        value: function navigate(index) {
            this.props.dispatch((0, _actions.setSelection)(index));
            this.gotoResults();
        }
    }, {
        key: 'gotoResults',
        value: function gotoResults() {
            // Is the results view within the viewport?
            var results = document.getElementsByClassName('search-result-navigator');
            if (results.length < 1) {
                results = document.getElementsByClassName('search-result-presenter');
            }

            if (results.length < 1) {
                return; // bail, as something's weird
            }

            var element = results[0];
            window.setTimeout(function () {
                return element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 500);
        }
    }, {
        key: 'gotoReference',
        value: function gotoReference(normalizedWord, urlChanged) {
            // Should we consider the URL as changed? This is the case when the back-button
            // in the browser is pressed. When this method however is manually triggered,
            // this may not be the case.
            if (urlChanged === undefined) {
                urlChanged = true;
            }

            var index = this.props.items ? this.props.items.findIndex(function (i) {
                return i.normalizedWord === normalizedWord;
            }) : -1;

            if (index > -1) {
                // Since the word exists in the search result set, update the current selection.
                // Make sure to update the _loadedWord_ property first, to cancel default behaviour
                // implemented in the _componentWillReceiveProps_ method.
                this.loadedWord = this.props.items[index].word;
                this.navigate(index);
            } else {
                // The word does not exist in the current result set.
                index = undefined;
            }

            this.props.dispatch((0, _actions.beginNavigation)(normalizedWord, undefined, index, !urlChanged));
        }
    }, {
        key: 'onNavigate',
        value: function onNavigate(ev, index) {
            ev.preventDefault();
            this.navigate(index);
        }

        /**
         * This is an unfortunate hack which implements default behavior for the forward and back buttons.
         * This method should be connected to the _popstate_ window event. It examines the URL of the previous
         * (or forward) state and determines whether the word (if found) exists within the current search
         * result set. If it is present, it navigates to the word, and dispatches a navigation signal.
         * 
         * @param {*} ev 
         */

    }, {
        key: 'onPopState',
        value: function onPopState(ev) {
            // The path name should be /w/<word>
            var path = location.pathname;
            if (path.substr(0, 3) !== '/w/') {
                return; // the browser is going somewhere else, so do nothing.
            }

            // retrieve the word and attempt to locate it within the search result set.
            var normalizedWord = decodeURIComponent(path.substr(3));
            this.gotoReference(normalizedWord);
        }
    }, {
        key: 'onPanelClick',
        value: function onPanelClick(ev) {
            ev.preventDefault();

            this.setState({
                itemsOpened: !this.state.itemsOpened
            });
        }
    }, {
        key: 'onReferenceLinkClick',
        value: function onReferenceLinkClick(ev) {
            this.gotoReference(ev.word, false);
        }

        /**
         * Receives a window message and deals with known messages.
         * @param {*} ev 
         */

    }, {
        key: 'onWindowMessage',
        value: function onWindowMessage(ev) {
            var domain = ev.origin || ev.originalEvent.origin;
            if (domain !== _edConfig2.default.messageDomain) {
                return;
            }

            var data = ev.data;
            switch (data.source) {
                case _edConfig2.default.messageNavigateName:
                    this.gotoReference(data.payload.word, false);
                    break;
            }
        }
    }, {
        key: 'renderSearchResults',
        value: function renderSearchResults() {
            var _this2 = this;

            var previousIndex = this.props.activeIndex - 1;
            var nextIndex = this.props.activeIndex + 1;

            if (previousIndex < 0) {
                previousIndex = this.props.items.length - 1;
            }

            if (nextIndex >= this.props.items.length - 1) {
                nextIndex = 0;
            }

            return _react2.default.createElement(
                'section',
                null,
                _react2.default.createElement(
                    'div',
                    { className: 'panel panel-default search-result-wrapper' },
                    _react2.default.createElement(
                        'div',
                        { className: 'panel-heading', onClick: this.onPanelClick.bind(this) },
                        _react2.default.createElement(
                            'h3',
                            { className: 'panel-title search-result-wrapper-toggler-title' },
                            _react2.default.createElement('span', { className: (0, _classnames2.default)('glyphicon', { 'glyphicon-minus': this.state.itemsOpened }, { 'glyphicon-plus': !this.state.itemsOpened }) }),
                            ' Matching words'
                        )
                    ),
                    _react2.default.createElement(
                        'div',
                        { className: (0, _classnames2.default)('panel-body', 'results-panel', { 'hidden': this.props.items.length < 1 || !this.state.itemsOpened }) },
                        _react2.default.createElement(
                            'div',
                            { className: 'row' },
                            _react2.default.createElement(
                                'div',
                                { className: 'col-xs-12' },
                                'These words match ',
                                _react2.default.createElement(
                                    'em',
                                    null,
                                    this.props.wordSearch
                                ),
                                '. Click on the one most relevant to you, or simply press enter to expand the first item in the list.'
                            )
                        ),
                        _react2.default.createElement(
                            'div',
                            { className: 'row' },
                            _react2.default.createElement(
                                'ul',
                                { className: 'search-result' },
                                this.props.items.map(function (item, i) {
                                    return _react2.default.createElement(_searchItem2.default, { key: i, active: i === _this2.props.activeIndex,
                                        item: item, index: i,
                                        onNavigate: _this2.navigate.bind(_this2) });
                                })
                            )
                        )
                    ),
                    _react2.default.createElement(
                        'div',
                        { className: (0, _classnames2.default)('panel-body', 'results-empty', { 'hidden': this.props.items.length > 0 }) },
                        _react2.default.createElement(
                            'div',
                            { className: 'row' },
                            _react2.default.createElement(
                                'div',
                                { className: 'col-xs-12' },
                                'Unfortunately, we were unable to find any words matching ',
                                _react2.default.createElement(
                                    'em',
                                    null,
                                    this.props.wordSearch
                                ),
                                '. Have you tried a synonym, or perhaps even an antonym?'
                            )
                        )
                    ),
                    _react2.default.createElement(
                        'div',
                        { className: (0, _classnames2.default)('panel-body', { 'hidden': this.state.itemsOpened }) },
                        this.props.items.length + ' matching words. Click on the title to expand.'
                    )
                ),
                this.props.items.length > 1 ? _react2.default.createElement(
                    'div',
                    { className: 'row search-result-navigator' },
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
                                    { href: '#', onClick: function onClick(ev) {
                                            return _this2.onNavigate(ev, previousIndex);
                                        } },
                                    '\u2190 ',
                                    this.props.items[previousIndex].word
                                )
                            ),
                            _react2.default.createElement(
                                'li',
                                { className: 'next' },
                                _react2.default.createElement(
                                    'a',
                                    { href: '#', onClick: function onClick(ev) {
                                            return _this2.onNavigate(ev, nextIndex);
                                        } },
                                    this.props.items[nextIndex].word,
                                    ' \u2192'
                                )
                            )
                        )
                    )
                ) : ''
            );
        }
    }, {
        key: 'renderBook',
        value: function renderBook() {
            var _this3 = this;

            return _react2.default.createElement(
                'section',
                null,
                _react2.default.createElement(
                    'div',
                    { className: 'search-result-presenter' },
                    this.props.bookData.sections.length < 1 ? _react2.default.createElement(
                        'div',
                        { 'class': 'row' },
                        _react2.default.createElement(
                            'h3',
                            null,
                            'Forsooth! I can\'t find what you\'re looking for!'
                        ),
                        _react2.default.createElement(
                            'p',
                            null,
                            'The word ',
                            _react2.default.createElement(
                                'em',
                                null,
                                this.props.bookData.word
                            ),
                            ' hasn\'t been recorded for any of the languages.'
                        )
                    ) : _react2.default.createElement(
                        'div',
                        { className: 'row' },
                        this.props.bookData.sections.map(function (s) {
                            return _react2.default.createElement(_bookSection2.default, { section: s,
                                key: s.language.id,
                                columnsMax: _this3.props.bookData.columnsMax,
                                columnsMid: _this3.props.bookData.columnsMid,
                                columnsMin: _this3.props.bookData.columnsMin,
                                onReferenceLinkClick: _this3.onReferenceLinkClick.bind(_this3) });
                        })
                    )
                )
            );
        }
    }, {
        key: 'render',
        value: function render() {

            var searchResults = null;
            if (Array.isArray(this.props.items)) {
                searchResults = this.renderSearchResults();
            }

            var book = null;
            if (this.props.bookData) {
                book = this.renderBook();
            }

            return _react2.default.createElement(
                'div',
                null,
                searchResults ? searchResults : '',
                book ? book : ''
            );
        }
    }]);

    return EDSearchResults;
}(_react2.default.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        items: state.items,
        loading: state.loading,
        activeIndex: state.itemIndex,
        bookData: state.bookData,
        wordSearch: state.wordSearch
    };
};

exports.default = (0, _reactRedux.connect)(mapStateToProps)(EDSearchResults);

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

/***/ 401:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(159);
__webpack_require__(160);
module.exports = __webpack_require__(163);


/***/ })

},[401]);