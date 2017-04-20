webpackJsonp([3,4],{

/***/ 154:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom__ = __webpack_require__(41);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_react_dom___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_react_dom__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__shared_components_markdown_editor__ = __webpack_require__(176);




window.addEventListener('load', function () {
    var textareas = document.querySelectorAll('textarea.ed-markdown-editor');

    var _iteratorNormalCompletion = true;
    var _didIteratorError = false;
    var _iteratorError = undefined;

    try {
        for (var _iterator = textareas[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var textarea = _step.value;

            __WEBPACK_IMPORTED_MODULE_1_react_dom___default.a.render(__WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(__WEBPACK_IMPORTED_MODULE_2__shared_components_markdown_editor__["a" /* default */], { componentName: textarea.name,
                value: textarea.value,
                rows: textarea.rows }), textarea.parentNode);
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
});

/***/ }),

/***/ 176:
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react__ = __webpack_require__(6);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_react__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_axios__ = __webpack_require__(40);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_axios___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_axios__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_classnames__ = __webpack_require__(12);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_classnames___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_classnames__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_html_to_react__ = __webpack_require__(30);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3_html_to_react___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3_html_to_react__);
var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

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

            this.setState({
                html: null,
                currentTab: tab
            });

            // Let the server render the Markdown code
            if (tab === MDMarkdownPreviewTab && !/^\s*$/.test(this.state.value)) {
                __WEBPACK_IMPORTED_MODULE_1_axios___default.a.post(window.EDConfig.api('/utility/markdown'), { markdown: this.state.value }).then(this.applyHtml.bind(this));
            }
        }
    }, {
        key: 'onValueChange',
        value: function onValueChange(ev) {
            this.setState({
                value: ev.target.value
            });
        }
    }, {
        key: 'render',
        value: function render() {
            var _this2 = this;

            var html = null;

            if (this.state.currentTab === MDMarkdownPreviewTab && this.state.html) {
                var parser = new __WEBPACK_IMPORTED_MODULE_3_html_to_react__["Parser"]();
                html = parser.parse(this.state.html);
            }

            return __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                'div',
                null,
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'ul',
                    { className: 'nav nav-tabs' },
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'li',
                        { role: 'presentation',
                            className: __WEBPACK_IMPORTED_MODULE_2_classnames___default()({ 'active': this.state.currentTab === MDMarkdownEditTab }) },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'a',
                            { href: '#', onClick: function onClick(e) {
                                    return _this2.onOpenTab(e, MDMarkdownEditTab);
                                } },
                            'Edit'
                        )
                    ),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'li',
                        { role: 'presentation',
                            className: __WEBPACK_IMPORTED_MODULE_2_classnames___default()({
                                'active': this.state.currentTab === MDMarkdownPreviewTab,
                                'disabled': !this.state.value
                            }) },
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'a',
                            { href: '#', onClick: function onClick(e) {
                                    return _this2.onOpenTab(e, MDMarkdownPreviewTab);
                                } },
                            'Preview'
                        )
                    )
                ),
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: __WEBPACK_IMPORTED_MODULE_2_classnames___default()({ 'hidden': this.state.currentTab !== MDMarkdownEditTab }) },
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement('textarea', { className: 'form-control',
                        name: this.props.componentName,
                        rows: this.props.rows,
                        value: this.state.value,
                        onChange: this.onValueChange.bind(this) }),
                    __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'small',
                        { className: 'pull-right' },
                        ' Supports Markdown. ',
                        __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                            'a',
                            { href: 'https://en.wikipedia.org/wiki/Markdown', target: '_blank' },
                            'Read more (opens a new window)'
                        ),
                        '.'
                    )
                ),
                __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                    'div',
                    { className: __WEBPACK_IMPORTED_MODULE_2_classnames___default()({ 'hidden': this.state.currentTab !== MDMarkdownPreviewTab }) },
                    html ? html : __WEBPACK_IMPORTED_MODULE_0_react___default.a.createElement(
                        'p',
                        null,
                        'Interpreting ...'
                    )
                )
            );
        }
    }]);

    return EDMarkdownEditor;
}(__WEBPACK_IMPORTED_MODULE_0_react___default.a.Component);

EDMarkdownEditor.defaultProps = {
    rows: 15,
    componentName: 'markdownBody'
};

/* harmony default export */ __webpack_exports__["a"] = EDMarkdownEditor;

/***/ }),

/***/ 380:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(154);


/***/ })

},[380]);