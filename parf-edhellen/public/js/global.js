webpackJsonp([1,3],{

/***/ 151:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom__ = __webpack_require__(49);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_react_dom__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_react_redux__ = __webpack_require__(38);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_redux__ = __webpack_require__(50);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_redux_thunk__ = __webpack_require__(353);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_redux_thunk___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4_redux_thunk__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__reducers__ = __webpack_require__(94);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__components_search_bar__ = __webpack_require__(173);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__components_search_results__ = __webpack_require__(175);









var store = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3_redux__["createStore"])(__WEBPACK_IMPORTED_MODULE_5__reducers__["a" /* EDSearchResultsReducer */], undefined /* <- preloaded state */
, __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3_redux__["applyMiddleware"])(__WEBPACK_IMPORTED_MODULE_4_redux_thunk___default.a));

window.addEventListener('load', function () {
    __WEBPACK_IMPORTED_MODULE_1_react_dom___default.a.render(__WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
        __WEBPACK_IMPORTED_MODULE_2_react_redux__["Provider"],
        { store: store },
        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
            'div',
            null,
            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_6__components_search_bar__["a" /* default */], null),
            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_7__components_search_results__["a" /* default */], null)
        )
    ), document.getElementById('search-component'));
});

/***/ }),

/***/ 152:
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 171:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_classnames__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_html_to_react__ = __webpack_require__(48);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_html_to_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_html_to_react__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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
        key: 'render',
        value: function render() {
            var gloss = this.props.gloss;
            var id = 'translation-block-' + gloss.TranslationID;

            var comments = null;
            if (gloss.Comments) {
                var parser = new __WEBPACK_IMPORTED_MODULE_2_html_to_react__["Parser"]();
                comments = parser.parse(gloss.Comments);
            }

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'blockquote',
                { itemscope: 'itemscope', itemtype: 'http://schema.org/Article', id: id, className: __WEBPACK_IMPORTED_MODULE_1_classnames___default()({ 'contribution': !gloss.Canon }) },
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'h3',
                    { rel: 'trans-word', className: 'trans-word' },
                    (!gloss.Canon || gloss.Uncertain) && gloss.Latest ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'a',
                        { href: '/about', title: 'Unverified or debatable content.' },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('span', { className: 'glyphicon glyphicon-question-sign' })
                    ) : '',
                    ' ',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { itemprop: 'headline' },
                        gloss.Word
                    ),
                    gloss.ExternalLinkFormat && gloss.ExternalID ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'a',
                        { href: gloss.ExternalLinkFormat.replace(/\{ExternalID\}/g, gloss.ExternalID),
                            className: 'ed-external-link-button',
                            title: 'Open on ' + gloss.TranslationGroup + ' (new tab/window)',
                            target: '_blank' },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('span', { 'class': 'glyphicon glyphicon-globe pull-right' })
                    ) : ''
                ),
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'p',
                    null,
                    gloss.Tengwar ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { className: 'tengwar' },
                        gloss.Tengwar
                    ) : '',
                    ' ',
                    gloss.Type != 'unset' ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { className: 'word-type', rel: 'trans-type' },
                        gloss.Type,
                        '.'
                    ) : '',
                    ' ',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { rel: 'trans-translation', itemprop: 'keywords' },
                        gloss.Translation
                    )
                ),
                comments,
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'footer',
                    null,
                    gloss.Source ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { className: 'word-source', rel: 'trans-source' },
                        '[',
                        gloss.Source,
                        ']'
                    ) : '',
                    ' ',
                    gloss.Etymology ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { className: 'word-etymology', rel: 'trans-etymology' },
                        gloss.Etymology,
                        '.'
                    ) : '',
                    ' ',
                    gloss.TranslationGroupID ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        null,
                        'Group: ',
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'span',
                            { itemprop: 'sourceOrganization' },
                            gloss.TranslationGroup,
                            '.'
                        )
                    ) : '',
                    ' Published: ',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { itemprop: 'datePublished' },
                        gloss.DateCreated
                    ),
                    ' by ',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'a',
                        { href: gloss.AuthorURL, itemprop: 'author', rel: 'author', title: 'View profile for ' + gloss.AuthorName + '.' },
                        gloss.AuthorName
                    )
                )
            );
        }
    }]);

    return EDBookGloss;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

/* harmony default export */ __webpack_exports__["a"] = EDBookGloss;

/***/ }),

/***/ 172:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__book_gloss__ = __webpack_require__(171);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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
        key: 'render',
        value: function render() {
            var className = 'col-sm-' + this.props.columnsMax + ' col-md-' + this.props.columnsMid + ' col-lg-' + this.props.columnsMin;
            var language = this.props.section.language;

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'article',
                { className: className },
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'header',
                    null,
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'h2',
                        { rel: 'language-box' },
                        language.Name,
                        '\xA0',
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'span',
                            { className: 'tengwar' },
                            language.Tengwar
                        )
                    )
                ),
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'section',
                    { className: 'language-box', id: 'language-box-' + language.ID },
                    this.props.section.glosses.map(function (g) {
                        return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_1__book_gloss__["a" /* default */], { gloss: g, language: language, key: g.TranslationID });
                    })
                )
            );
        }
    }]);

    return EDBookSection;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

/* harmony default export */ __webpack_exports__["a"] = EDBookSection;

/***/ }),

/***/ 173:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_redux__ = __webpack_require__(38);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_classnames__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_classnames__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__actions__ = __webpack_require__(93);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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
                    ID: 0,
                    Name: 'All languages'
                }].concat(_toConsumableArray(JSON.parse(languageNode.textContent)))
            });
        }
    }, {
        key: 'searchKeyDown',
        value: function searchKeyDown(ev) {
            var direction = ev.which === 40 ? 1 : ev.which === 38 ? -1 : undefined;

            if (direction !== undefined) {
                ev.preventDefault();
                this.props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3__actions__["c" /* advanceSelection */])(direction));
            }
        }
    }, {
        key: 'wordChange',
        value: function wordChange(ev) {
            this.setState({
                word: ev.target.value
            });

            this.search();
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
        value: function search() {
            var _this2 = this;

            if (/^\s*$/.test(this.state.word)) {
                return; // empty search result
            }

            if (this.throttle) {
                window.clearTimeout(this.throttle);
            }

            this.throttle = window.setTimeout(function () {
                _this2.props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3__actions__["d" /* fetchResults */])(_this2.state.word, _this2.state.isReversed, _this2.state.languageId));
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
                this.props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3__actions__["b" /* setSelection */])(0));
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var fieldClasses = __WEBPACK_IMPORTED_MODULE_2_classnames___default()('form-control', { 'disabled': this.props.loading });
            var statusClasses = __WEBPACK_IMPORTED_MODULE_2_classnames___default()('glyphicon', this.props.loading ? 'glyphicon-refresh loading' : 'glyphicon-search');

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'form',
                { onSubmit: this.navigate.bind(this) },
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: 'row' },
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: 'col-md-12' },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'div',
                            { className: 'input-group input-group-lg' },
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'span',
                                { className: 'input-group-addon' },
                                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                    'span',
                                    { className: statusClasses },
                                    ' '
                                )
                            ),
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('input', { type: 'search', className: fieldClasses,
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
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: 'row' },
                    this.state.languages ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'select',
                        { className: 'search-language-select', onChange: this.languageChange.bind(this) },
                        this.state.languages.map(function (l) {
                            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'option',
                                { value: l.ID, key: l.ID },
                                l.Name
                            );
                        })
                    ) : '',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: 'checkbox input-sm search-reverse-box-wrapper' },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'label',
                            null,
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('input', { type: 'checkbox', name: 'isReversed',
                                checked: this.state.isReversed,
                                onChange: this.reverseChange.bind(this) }),
                            ' Reverse search'
                        )
                    )
                )
            );
        }
    }]);

    return EDSearchBar;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        loading: state.loading
    };
};

/* harmony default export */ __webpack_exports__["a"] = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_react_redux__["connect"])(mapStateToProps)(EDSearchBar);

/***/ }),

/***/ 174:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_classnames__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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
            var cssClass = __WEBPACK_IMPORTED_MODULE_1_classnames___default()({ 'selected': this.props.active });
            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'li',
                null,
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'a',
                    { href: '#', className: cssClass, onClick: this.navigate.bind(this) },
                    this.props.item.word
                )
            );
        }
    }]);

    return EDSearchItem;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

/* harmony default export */ __webpack_exports__["a"] = EDSearchItem;

/***/ }),

/***/ 175:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_redux__ = __webpack_require__(38);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__actions__ = __webpack_require__(93);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_classnames__ = __webpack_require__(25);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_classnames__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__search_item__ = __webpack_require__(174);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__book_section__ = __webpack_require__(172);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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
        return _this;
    }

    _createClass(EDSearchResults, [{
        key: 'componentWillMount',
        value: function componentWillMount() {
            window.addEventListener('popstate', this.popStateHandler);
        }
    }, {
        key: 'componentWillUnmount',
        value: function componentWillUnmount() {
            window.removeEventListener(this.popStateHandler);
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
            props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_2__actions__["a" /* beginNavigation */])(item.word, item.normalizedWord, this.loadedIndex));
        }
    }, {
        key: 'navigate',
        value: function navigate(index, word, normalizedWord) {
            this.props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_2__actions__["b" /* setSelection */])(index));
            this.gotoResults();
        }
    }, {
        key: 'gotoResults',
        value: function gotoResults() {
            // Is the results view within the viewport?
            var results = document.getElementsByClassName('search-result-presenter');
            if (results.length < 1) {
                return; // doesn't exist - no results?
            }

            if (undefined === results[0].scrollIntoView) {
                return; // Lacking browser support!
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
            var index = this.props.items ? this.props.items.findIndex(function (i) {
                return i.normalizedWord === normalizedWord;
            }) : -1;

            if (index > -1) {
                // Since the word exists in the search result set, update the current selection.
                // Make sure to update the _loadedWord_ property first, to cancel default behaviour
                // implemented in the _componentWillReceiveProps_ method.
                this.loadedWord = this.props.items[index].word;
                this.navigate(index);
            }

            this.props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_2__actions__["a" /* beginNavigation */])(normalizedWord, undefined, undefined, false));
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
        key: 'render',
        value: function render() {
            var _this2 = this;

            if (!Array.isArray(this.props.items)) {
                return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('div', null);
            }

            var previousIndex = this.props.activeIndex - 1;
            var nextIndex = this.props.activeIndex + 1;

            if (previousIndex < 0) {
                previousIndex = this.props.items.length - 1;
            }

            if (nextIndex >= this.props.items.length - 1) {
                nextIndex = 0;
            }

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'div',
                null,
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: 'panel panel-default search-result-wrapper' },
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: 'panel-heading', onClick: this.onPanelClick.bind(this) },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'h3',
                            { className: 'panel-title search-result-wrapper-toggler-title' },
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('span', { className: __WEBPACK_IMPORTED_MODULE_3_classnames___default()('glyphicon', { 'glyphicon-minus': this.state.itemsOpened }, { 'glyphicon-plus': !this.state.itemsOpened }) }),
                            ' Matching words'
                        )
                    ),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: __WEBPACK_IMPORTED_MODULE_3_classnames___default()('panel-body', 'results-panel', { 'hidden': this.props.items.length < 1 || !this.state.itemsOpened }) },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'div',
                            { className: 'row' },
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'div',
                                { className: 'col-xs-12' },
                                'These words match ',
                                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                    'em',
                                    null,
                                    this.props.wordSearch
                                ),
                                '. Click on the one most relevant to you, or simply press enter to expand the first item in the list.'
                            )
                        ),
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'div',
                            { className: 'row' },
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'ul',
                                { className: 'search-result' },
                                this.props.items.map(function (item, i) {
                                    return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_4__search_item__["a" /* default */], { key: i, active: i === _this2.props.activeIndex,
                                        item: item, index: i,
                                        onNavigate: _this2.navigate.bind(_this2) });
                                })
                            )
                        )
                    ),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: __WEBPACK_IMPORTED_MODULE_3_classnames___default()('panel-body', 'results-empty', { 'hidden': this.props.items.length > 0 }) },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'div',
                            { className: 'row' },
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'div',
                                { className: 'col-xs-12' },
                                'Unfortunately, we were unable to find any words matching ',
                                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                    'em',
                                    null,
                                    this.props.wordSearch
                                ),
                                '. Have you tried a synonym, or perhaps even an antonym?'
                            )
                        )
                    ),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: __WEBPACK_IMPORTED_MODULE_3_classnames___default()('panel-body', { 'hidden': this.state.itemsOpened }) },
                        this.props.items.length + ' matching words. Click on the title to expand.'
                    )
                ),
                this.props.items.length > 1 ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: 'row search-result-navigator' },
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'nav',
                        null,
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'ul',
                            { className: 'pager' },
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'li',
                                { className: 'previous' },
                                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                    'a',
                                    { href: '#', onClick: function onClick(ev) {
                                            return _this2.onNavigate(ev, previousIndex);
                                        } },
                                    '\u2190 ',
                                    this.props.items[previousIndex].word
                                )
                            ),
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'li',
                                { className: 'next' },
                                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
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
                ) : '',
                this.props.bookData ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: 'search-result-presenter' },
                    this.props.bookData.sections.length < 1 ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { 'class': 'row' },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'h3',
                            null,
                            'Forsooth! I can\'t find what you\'re looking for!'
                        ),
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'p',
                            null,
                            'The word ',
                            __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                                'em',
                                null,
                                this.props.bookData.word
                            ),
                            ' hasn\'t been recorded for any of the languages.'
                        )
                    ) : __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        { className: 'row' },
                        this.props.bookData.sections.map(function (s) {
                            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_5__book_section__["a" /* default */], { section: s,
                                key: s.language.ID,
                                columnsMax: _this2.props.bookData.columnsMax,
                                columnsMid: _this2.props.bookData.columnsMid,
                                columnsMin: _this2.props.bookData.columnsMin });
                        })
                    )
                ) : ''
            );
        }
    }]);

    return EDSearchResults;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        items: state.items,
        loading: state.loading,
        activeIndex: state.itemIndex,
        bookData: state.bookData,
        wordSearch: state.wordSearch
    };
};

/* harmony default export */ __webpack_exports__["a"] = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_react_redux__["connect"])(mapStateToProps)(EDSearchResults);

/***/ }),

/***/ 353:
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

/***/ 372:
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(151);
module.exports = __webpack_require__(152);


/***/ }),

/***/ 93:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios__ = __webpack_require__(47);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_axios__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__reducers__ = __webpack_require__(94);
/* unused harmony export requestResults */
/* unused harmony export receiveResults */
/* unused harmony export requestNavigation */
/* unused harmony export receiveNavigation */
/* harmony export (immutable) */ __webpack_exports__["c"] = advanceSelection;
/* harmony export (immutable) */ __webpack_exports__["b"] = setSelection;
/* harmony export (immutable) */ __webpack_exports__["d"] = fetchResults;
/* harmony export (immutable) */ __webpack_exports__["a"] = beginNavigation;



function requestResults(wordSearch) {
    return {
        type: __WEBPACK_IMPORTED_MODULE_1__reducers__["b" /* REQUEST_RESULTS */],
        wordSearch: wordSearch
    };
}

function receiveResults(results) {
    return {
        type: __WEBPACK_IMPORTED_MODULE_1__reducers__["c" /* RECEIVE_RESULTS */],
        items: results
    };
}

function requestNavigation(word, normalizedWord, index) {
    return {
        type: __WEBPACK_IMPORTED_MODULE_1__reducers__["d" /* REQUEST_NAVIGATION */],
        word: word,
        normalizedWord: normalizedWord,
        index: index
    };
}

function receiveNavigation(bookData) {
    return {
        type: __WEBPACK_IMPORTED_MODULE_1__reducers__["e" /* RECEIVE_NAVIGATION */],
        bookData: bookData
    };
}

function advanceSelection(direction) {
    return {
        type: __WEBPACK_IMPORTED_MODULE_1__reducers__["f" /* ADVANCE_SELECTION */],
        direction: direction > 0 ? 1 : -1
    };
}

function setSelection(index) {
    return {
        type: __WEBPACK_IMPORTED_MODULE_1__reducers__["g" /* SET_SELECTION */],
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
        __WEBPACK_IMPORTED_MODULE_0_axios___default.a.post('/api/v1/book/find', { word: word, reversed: reversed, languageId: languageId }).then(function (resp) {
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

    var uriEncodedWord = encodeURIComponent(normalizedWord || word);
    var apiAddress = '/api/v1/book/translate';
    var address = '/w/' + uriEncodedWord;
    var title = word + ' - Parf Edhellen';

    if (modifyState) {
        window.history.pushState(null, title, address);
    }
    document.title = title; // because most browsers doesn't change the document title when pushing state

    return function (dispatch) {
        dispatch(requestNavigation(word, normalizedWord || undefined, index || undefined));

        __WEBPACK_IMPORTED_MODULE_0_axios___default.a.post(apiAddress, { word: normalizedWord || word }).then(function (resp) {
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

/***/ 94:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return REQUEST_RESULTS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "d", function() { return REQUEST_NAVIGATION; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return RECEIVE_RESULTS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "e", function() { return RECEIVE_NAVIGATION; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "f", function() { return ADVANCE_SELECTION; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "g", function() { return SET_SELECTION; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return EDSearchResultsReducer; });
var REQUEST_RESULTS = 'EDSR_REQUEST_RESULTS';
var REQUEST_NAVIGATION = 'EDSR_REQUEST_NAVIGATION';
var RECEIVE_RESULTS = 'EDSR_RECEIVE_RESULTS';
var RECEIVE_NAVIGATION = 'EDSR_RECEIVE_NAVIGATION';
var ADVANCE_SELECTION = 'EDSR_ADVANCE_SELECTION';
var SET_SELECTION = 'EDSR_SET_SELECTION';

var EDSearchResultsReducer = function EDSearchResultsReducer() {
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
            return Object.assign({}, state, {
                loading: true,
                word: action.word,
                normalizedWord: action.normalizedWord,
                itemIndex: action.index || state.itemIndex
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

/***/ })

},[372]);