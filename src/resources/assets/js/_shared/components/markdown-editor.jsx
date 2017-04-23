import React from 'react';
import axios from 'axios';
import classNames from 'classnames';
import { Parser as HtmlToReactParser } from 'html-to-react';

const MDMarkdownEditTab = 0;
const MDMarkdownPreviewTab = 1;

class EDMarkdownEditor extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            value: this.props.value || '',
            currentTab: MDMarkdownEditTab
        };
    }

    applyHtml(resp) {
        this.setState({
            html: resp.data.html
        });
    }

    onOpenTab(ev, tab) {
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
            axios.post(window.EDConfig.api('/utility/markdown'), { markdown: this.state.value })
                .then(this.applyHtml.bind(this));
        }
    }

    onValueChange(ev) {
        this.setState({
            value: ev.target.value
        });
    }

    render() {
        let html = null;
        
        if (this.state.currentTab === MDMarkdownPreviewTab && this.state.html) {
            var parser = new HtmlToReactParser();
            html = parser.parse(this.state.html);
        }

        return (
            <div>
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
                          rows={this.props.rows}
                          value={this.state.value}
                          onChange={this.onValueChange.bind(this)} />
                    <small className="pull-right">
                        {' Supports Markdown. '}
                        <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">
                            Read more (opens a new window)
                        </a>.
                    </small>
                </div>
                <div className={classNames({ 'hidden': this.state.currentTab !== MDMarkdownPreviewTab })}>
                    {html ? html : <p>Interpreting ...</p>}
                </div>
            </div>
        );
    }
}

EDMarkdownEditor.defaultProps = {
    rows: 15,
    componentName: 'markdownBody'
};

export default EDMarkdownEditor;