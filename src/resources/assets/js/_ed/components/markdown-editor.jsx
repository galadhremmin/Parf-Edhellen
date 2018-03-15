import React from 'react';
import EDAPI from 'ed-api';
import classNames from 'classnames';
import EDConfig from 'ed-config';
import { Parser as HtmlToReactParser } from 'html-to-react';

const MDMarkdownEditTab = 0;
const MDMarkdownPreviewTab = 1;

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
