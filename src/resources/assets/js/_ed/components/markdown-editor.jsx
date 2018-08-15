import React from 'react';
import EDAPI from 'ed-api';
import classNames from 'classnames';
import { Parser as HtmlToReactParser } from 'html-to-react';

const MDMarkdownEditTab = 0;
const MDMarkdownPreviewTab = 1;
const MDMarkdownSyntaxTab = 2;

class EDMarkdownEditor extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            value: props.value || '',
            currentTab: MDMarkdownEditTab
        };
    }

    componentWillReceiveProps(props) {
        // check if the value prop has changed, as it is the one property which
        // will most likely be affected by Redux.
        if (props.value !== undefined && this.state.value !== props.value) {
            this.setValue(props.value || '');
        }
    }

    applyHtml(resp) {
        this.setState({
            html: resp.data.html
        });
    }

    /**
     * Sets the component's current value.
     * @param {string} value 
     */
    setValue(value) {
        const originalValue = this.state.value;
        this.setState({
            value
        });

        if (originalValue !== value) {
            this.triggerChange();
        }
    }

    /**
     * Gets the component's current value.
     */
    getValue() {
        return this.state.value;
    }

    triggerChange() {
        if (typeof this.props.onChange === 'function') {
            window.setTimeout(() => {
                this.props.onChange({
                    target: this,
                    value: this.getValue()
                });
            }, 0);
        }
    }

    onOpenTab(ev, tab) {
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
            const boundingRect =  this.textArea.getBoundingClientRect();
            this.markupContainer.style.minHeight = boundingRect.height + 'px';

            // Let the server parse the markdown
            EDAPI.post('utility/markdown', { markdown: this.state.value })
                .then(this.applyHtml.bind(this));
        }

        this.setState({
            html: null,
            currentTab: tab
        });
    }

    onValueChange(ev) {
        this.setValue(ev.target.value);
    }

    render() {
        let html = null;
        
        if (this.state.currentTab === MDMarkdownPreviewTab && this.state.html) {
            var parser = new HtmlToReactParser();
            html = parser.parse(this.state.html);
        }

        return (
            <div className="clearfix">
                <ul className="nav nav-tabs">
                    <li role="presentation"
                        className={classNames({'active': this.state.currentTab === MDMarkdownEditTab})}>
                        <a href="#" onClick={e => this.onOpenTab(e, MDMarkdownEditTab)}>Edit</a>
                    </li>
                    <li role="presentation"
                        className={classNames({
                            'active': this.state.currentTab === MDMarkdownSyntaxTab
                         })}>
                         <a href="#" onClick={e => this.onOpenTab(e, MDMarkdownSyntaxTab)}>Formatting help</a>
                    </li>
                    <li role="presentation"
                        className={classNames({
                            'active': this.state.currentTab === MDMarkdownPreviewTab,
                            'disabled': !this.state.value
                         })}>
                        <a href="#" onClick={e => this.onOpenTab(e, MDMarkdownPreviewTab)}>Preview</a>
                    </li>
                </ul>
                <div className={classNames({ 'hidden': this.state.currentTab !== MDMarkdownEditTab })}>
                    <textarea className="form-control"
                          name={this.props.componentName}
                          id={this.props.componentId}
                          rows={this.props.rows}
                          value={this.state.value}
                          onChange={this.onValueChange.bind(this)}
                          ref={textarea => this.textArea = textarea}
                          {...(this.props.componentProps || {})} />
                    <small className="pull-right">
                        {' Supports Markdown. '}
                        <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">
                            Read more (opens a new window)
                        </a>.
                    </small>
                </div>
                <div className={classNames({ 'hidden': this.state.currentTab !== MDMarkdownSyntaxTab })}>
                    <p>
                        Markdown is a lightweight markup language with plain text formatting syntax. 
                        It is designed to make it easy for you to apply formatting to your text with 
                        minimal impact on your content.
                    </p>
                    <p>
                        We support the following Markdown syntax:
                    </p>
                    <table className="table table-striped">
                        <thead>
                            <tr>
                                <th>Syntax</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>*Italics*</code></td>
                                <td><em>Italics</em> for emphasis.</td>
                            </tr>
                            <tr>
                                <td><code>**Bold text**</code></td>
                                <td><b>Bold text</b> for emphasis.</td>
                            </tr>
                            <tr>
                                <td><code>~~Strike-through~~</code></td>
                                <td><s>Strike-through</s></td>
                            </tr>
                            <tr>
                                <td><code>![An exclamation](exclamation-glyph.png)</code></td>
                                <td>
                                    <span className="glyphicon glyphicon-exclamation-sign" title="An exclamation" />. 
                                    Displays an image with an alternate text (if the image fails to load).
                                </td>
                            </tr>
                            <tr>
                                <td><code>[[tree]]</code></td>
                                <td>Link to the dictionary entry for <em>tree</em>.</td>
                            </tr>
                            <tr>
                                <td><code>[Link to trees](https://en.wikipedia.org/wiki/Tree)</code></td>
                                <td>
                                    <a href="https://en.wikipedia.org/wiki/Tree" target="_blank">
                                        Link to trees
                                    </a>
                                    . Links to the specified page. 
                                    Any external and internal addresses may be specified.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <code>
                                        * item 1<br />
                                        * item 2<br />
                                        &nbsp;&nbsp;&nbsp;* sub item 1<br />
                                        * item 3
                                    </code>
                                </td>
                                <td>
                                    <ul>
                                        <li>item 1</li>
                                        <li>
                                            item 2
                                            <ul>
                                                <li>sub item 1</li>
                                            </ul>
                                        </li>
                                        <li>item 3</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <code>
                                        1. run<br />
                                        2. hide
                                    </code>
                                </td>
                                <td>
                                    <ol>
                                        <li>run</li>
                                        <li>hide</li>
                                    </ol>
                                </td>
                            </tr>
                            <tr>
                                <td><code># Header</code></td>
                                <td>1st level header. <em>Please use with care!</em></td>
                            </tr>
                            <tr>
                                <td><code>## Header</code></td>
                                <td>2nd level header. <em>Please use with care!</em></td>
                            </tr>
                            <tr>
                                <td><code>### Header</code></td>
                                <td>3rd level header. <em>Please use with care!</em></td>
                            </tr>
                            <tr>
                                <td><code>@sindarin|mae govannen!@</code></td>
                                <td>
                                    Transcribes <em>mae govannen</em> to <span className="tengwar">{'tlE xr^5{#5$Á'}</span> {' '}
                                    We use Glaemscribe for transcriptions. Supported modes are: {' '}
                                    adunaic, blackspeech, quenya, sindarin-beleriand, sindarin, telerin, and westron.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p>
                        More information about Markdown 
                        {' '}
                        <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">
                            can be found on Wikipedia
                        </a>.
                    </p>
                </div>
                <div className={classNames({ 'hidden': this.state.currentTab !== MDMarkdownPreviewTab })} 
                    ref={container => this.markupContainer = container}>
                    {html ? html : <p>Interpreting ...</p>}
                </div>
            </div>
        );
    }
}

EDMarkdownEditor.defaultProps = {
    rows: 15,
    componentName: 'markdownBody',
    componentId: 'markdownBody',
    componentProps: undefined
};

export default EDMarkdownEditor;
