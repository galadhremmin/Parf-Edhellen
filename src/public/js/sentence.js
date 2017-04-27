webpackJsonp([3],{

/***/ 152:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(31);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _reactRedux = __webpack_require__(18);

var _redux = __webpack_require__(32);

var _reduxThunk = __webpack_require__(40);

var _reduxThunk2 = _interopRequireDefault(_reduxThunk);

var _reducers = __webpack_require__(93);

var _reducers2 = _interopRequireDefault(_reducers);

var _fragmentExplorer = __webpack_require__(179);

var _fragmentExplorer2 = _interopRequireDefault(_fragmentExplorer);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

var store = (0, _redux.createStore)(_reducers2.default, undefined /* <- preloaded state */
, (0, _redux.applyMiddleware)(_reduxThunk2.default));

window.addEventListener('load', function () {
    _reactDom2.default.render(_react2.default.createElement(
        _reactRedux.Provider,
        { store: store },
        _react2.default.createElement(_fragmentExplorer2.default, null)
    ), document.getElementById('ed-fragment-navigator'));
});

/***/ }),

/***/ 176:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
    value: true
});
exports.selectFragment = selectFragment;

var _axios = __webpack_require__(21);

var _axios2 = _interopRequireDefault(_axios);

var _reducers = __webpack_require__(93);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function selectFragment(fragmentId, translationId) {
    return function (dispatch) {
        dispatch({
            type: _reducers.REQUEST_FRAGMENT,
            fragmentId: fragmentId
        });

        var start = new Date().getTime();
        _axios2.default.get(window.EDConfig.api('/book/translate/' + translationId)).then(function (resp) {
            // Enable the animation to play at least 800 milliseconds.
            var animationDelay = -Math.min(0, new Date().getTime() - start - 800);

            window.setTimeout(function () {
                dispatch({
                    type: _reducers.RECEIVE_FRAGMENT,
                    bookData: resp.data,
                    translationId: translationId
                });
            }, animationDelay);
        });
    };
}

/***/ }),

/***/ 179:
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

var _actions = __webpack_require__(176);

var _fragment = __webpack_require__(180);

var _fragment2 = _interopRequireDefault(_fragment);

var _tengwarFragment = __webpack_require__(181);

var _tengwarFragment2 = _interopRequireDefault(_tengwarFragment);

var _bookGloss = __webpack_require__(52);

var _bookGloss2 = _interopRequireDefault(_bookGloss);

var _htmlToReact = __webpack_require__(27);

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
            window.location.hash = '!' + ev.id;

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
            window.EDConfig.message(window.EDConfig.messageNavigateName, data);
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

exports.default = (0, _reactRedux.connect)(mapStateToProps)(EDFragmentExplorer);

/***/ }),

/***/ 180:
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

/***/ 181:
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

/***/ }),

/***/ 383:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(152);


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

/***/ 52:
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

var _htmlToReact = __webpack_require__(27);

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

/***/ 93:
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

/***/ })

},[383]);