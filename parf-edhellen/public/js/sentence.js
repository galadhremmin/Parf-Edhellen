webpackJsonp([2,4],{

/***/ 156:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_react_dom__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_react_redux__ = __webpack_require__(26);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_redux__ = __webpack_require__(41);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_redux_thunk__ = __webpack_require__(86);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4_redux_thunk___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4_redux_thunk__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__reducers__ = __webpack_require__(96);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__components_fragment_explorer__ = __webpack_require__(182);








var store = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3_redux__["createStore"])(__WEBPACK_IMPORTED_MODULE_5__reducers__["a" /* default */], undefined /* <- preloaded state */
, __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_3_redux__["applyMiddleware"])(__WEBPACK_IMPORTED_MODULE_4_redux_thunk___default.a));

window.addEventListener('load', function () {
    __WEBPACK_IMPORTED_MODULE_1_react_dom___default.a.render(__WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
        __WEBPACK_IMPORTED_MODULE_2_react_redux__["Provider"],
        { store: store },
        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_6__components_fragment_explorer__["a" /* default */], null)
    ), document.getElementById('ed-fragment-navigator'));
});

/***/ }),

/***/ 176:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_classnames__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_html_to_react__ = __webpack_require__(50);
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
        key: 'processHtml',
        value: function processHtml(html) {
            var _this2 = this;

            var definitions = new __WEBPACK_IMPORTED_MODULE_2_html_to_react__["ProcessNodeDefinitions"](__WEBPACK_IMPORTED_MODULE_0_react___default.a);
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

                    return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
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

            var parser = new __WEBPACK_IMPORTED_MODULE_2_html_to_react__["Parser"]();
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
            var id = 'translation-block-' + gloss.TranslationID;

            var comments = null;
            if (gloss.Comments) {
                comments = this.processHtml(gloss.Comments);
            }

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'blockquote',
                { itemScope: 'itemscope', itemType: 'http://schema.org/Article', id: id, className: __WEBPACK_IMPORTED_MODULE_1_classnames___default()({ 'contribution': !gloss.Canon }) },
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
                        { itemProp: 'headline' },
                        gloss.Word
                    ),
                    gloss.ExternalLinkFormat && gloss.ExternalID ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'a',
                        { href: gloss.ExternalLinkFormat.replace(/\{ExternalID\}/g, gloss.ExternalID),
                            className: 'ed-external-link-button',
                            title: 'Open on ' + gloss.TranslationGroup + ' (new tab/window)',
                            target: '_blank' },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('span', { className: 'glyphicon glyphicon-globe pull-right' })
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
                        { rel: 'trans-translation', itemProp: 'keywords' },
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
                            { itemProp: 'sourceOrganization' },
                            gloss.TranslationGroup,
                            '.'
                        )
                    ) : '',
                    ' Published: ',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'span',
                        { itemProp: 'datePublished' },
                        gloss.DateCreated
                    ),
                    ' by ',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'a',
                        { href: gloss.AuthorURL, itemProp: 'author', rel: 'author', title: 'View profile for ' + gloss.AuthorName + '.' },
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

/***/ 181:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios__ = __webpack_require__(39);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_axios__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__reducers__ = __webpack_require__(96);
/* harmony export (immutable) */ __webpack_exports__["a"] = selectFragment;



function selectFragment(fragmentId, translationId) {
    return function (dispatch) {
        dispatch({
            type: __WEBPACK_IMPORTED_MODULE_1__reducers__["b" /* REQUEST_FRAGMENT */],
            fragmentId: fragmentId
        });

        var start = new Date().getTime();
        __WEBPACK_IMPORTED_MODULE_0_axios___default.a.get(window.EDConfig.api('/book/translate/' + translationId)).then(function (resp) {
            // Enable the animation to play at least 800 milliseconds.
            var animationDelay = -Math.min(0, new Date().getTime() - start - 800);

            window.setTimeout(function () {
                dispatch({
                    type: __WEBPACK_IMPORTED_MODULE_1__reducers__["c" /* RECEIVE_FRAGMENT */],
                    bookData: resp.data,
                    translationId: translationId
                });
            }, animationDelay);
        });
    };
}

/***/ }),

/***/ 182:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_redux__ = __webpack_require__(26);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__actions__ = __webpack_require__(181);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__fragment__ = __webpack_require__(183);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__tengwar_fragment__ = __webpack_require__(184);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__search_components_book_gloss__ = __webpack_require__(176);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_html_to_react__ = __webpack_require__(50);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6_html_to_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_6_html_to_react__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }









var EDFragmentExplorer = function (_React$Component) {
    _inherits(EDFragmentExplorer, _React$Component);

    function EDFragmentExplorer(props) {
        _classCallCheck(this, EDFragmentExplorer);

        return _possibleConstructorReturn(this, (EDFragmentExplorer.__proto__ || Object.getPrototypeOf(EDFragmentExplorer)).call(this, props));
    }

    _createClass(EDFragmentExplorer, [{
        key: 'componentDidMount',
        value: function componentDidMount() {}
    }, {
        key: 'onFragmentClick',
        value: function onFragmentClick(ev) {
            this.props.dispatch(__webpack_require__.i(__WEBPACK_IMPORTED_MODULE_2__actions__["a" /* selectFragment */])(ev.id, ev.translationId));
        }
    }, {
        key: 'render',
        value: function render() {
            var _this2 = this;

            var section = null;
            var fragment = null;
            var parser = null;

            if (!this.props.loading && this.props.bookData && this.props.bookData.sections.length > 0) {
                section = this.props.bookData.sections[0];
                fragment = this.props.fragments.find(function (f) {
                    return f.id === _this2.props.fragmentId;
                });
                parser = new __WEBPACK_IMPORTED_MODULE_6_html_to_react__["Parser"]();
            }

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'div',
                { className: 'well ed-fragment-navigator' },
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'p',
                    { className: 'tengwar ed-tengwar-fragments' },
                    this.props.fragments.map(function (f) {
                        return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_4__tengwar_fragment__["a" /* default */], { fragment: f,
                            key: 'tng' + f.id,
                            selected: f.id === _this2.props.fragmentId });
                    })
                ),
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'p',
                    { className: 'ed-elvish-fragments' },
                    this.props.fragments.map(function (f) {
                        return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_3__fragment__["a" /* default */], { fragment: f,
                            key: 'frg' + f.id,
                            selected: f.id === _this2.props.fragmentId,
                            onClick: _this2.onFragmentClick.bind(_this2) });
                    })
                ),
                this.props.loading ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('div', { className: 'sk-spinner sk-spinner-pulse' }) : section ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    null,
                    fragment.grammarType ? __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        null,
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'em',
                            null,
                            fragment.grammarType
                        )
                    ) : '',
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        null,
                        fragment.comments ? parser.parse(fragment.comments) : ''
                    ),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('hr', null),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'div',
                        null,
                        section.glosses.map(function (g) {
                            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_5__search_components_book_gloss__["a" /* default */], { gloss: g,
                                language: section.language,
                                key: g.TranslationID });
                        })
                    )
                ) : ''
            );
        }
    }]);

    return EDFragmentExplorer;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

var mapStateToProps = function mapStateToProps(state) {
    return {
        fragments: state.fragments,
        fragmentId: state.fragmentId,
        bookData: state.bookData,
        loading: state.loading
    };
};

/* harmony default export */ __webpack_exports__["a"] = __webpack_require__.i(__WEBPACK_IMPORTED_MODULE_1_react_redux__["connect"])(mapStateToProps)(EDFragmentExplorer);

/***/ }),

/***/ 183:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_classnames__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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
                    translationId: this.props.fragment.translateId
                });
            }
        }
    }, {
        key: 'render',
        value: function render() {
            var f = this.props.fragment;

            if (f.interpunctuation) {
                return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'span',
                    null,
                    f.fragment
                );
            }

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'span',
                null,
                ' ',
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'a',
                    { className: __WEBPACK_IMPORTED_MODULE_1_classnames___default()({ 'active': this.props.selected }),
                        href: '/wt/' + f.translateId,
                        onClick: this.onFragmentClick.bind(this) },
                    f.fragment
                )
            );
        }
    }]);

    return EDFragment;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

/* harmony default export */ __webpack_exports__["a"] = EDFragment;

/***/ }),

/***/ 184:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames__ = __webpack_require__(16);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_classnames__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'span',
                { className: __WEBPACK_IMPORTED_MODULE_1_classnames___default()({ 'active': this.props.selected }) },
                (f.interpunctuation ? '' : ' ') + f.tengwar
            );
        }
    }]);

    return EDTengwarFragment;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

/* harmony default export */ __webpack_exports__["a"] = EDTengwarFragment;

/***/ }),

/***/ 381:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(156);


/***/ }),

/***/ 86:
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

/***/ 96:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return REQUEST_FRAGMENT; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return RECEIVE_FRAGMENT; });
var REQUEST_FRAGMENT = 'EDSR_REQUEST_FRAGMENT';
var RECEIVE_FRAGMENT = 'EDSR_RECEIVE_FRAGMENT';

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

/* harmony default export */ __webpack_exports__["a"] = EDSentenceReducer;

/***/ })

},[381]);