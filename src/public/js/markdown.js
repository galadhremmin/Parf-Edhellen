webpackJsonp([4],{

/***/ 148:
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(31);

var _reactDom2 = _interopRequireDefault(_reactDom);

var _markdownEditor = __webpack_require__(42);

var _markdownEditor2 = _interopRequireDefault(_markdownEditor);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

window.addEventListener('load', function () {
    var textareas = document.querySelectorAll('textarea.ed-markdown-editor');

    var _iteratorNormalCompletion = true;
    var _didIteratorError = false;
    var _iteratorError = undefined;

    try {
        for (var _iterator = textareas[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
            var textarea = _step.value;

            _reactDom2.default.render(_react2.default.createElement(_markdownEditor2.default, { componentName: textarea.name,
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

/***/ 381:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(148);


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

/***/ })

},[381]);