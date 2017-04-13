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

    openTab(ev, tab) {
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
            axios.post('/api/v1/utility/markdown', { markdown: this.state.value })
                .then(this.applyHtml.bind(this));
        }
    }

    applyHtml(resp) {
        this.setState({
            html: resp.data.html
        });
    }

    onValueChange(ev) {
        this.setState({
            value: ev.target.value
        });
    }

    render() {
        let html = null;
        
        if (this.state.html) {
            var parser = new HtmlToReactParser();
            html = parser.parse(this.state.html);
        }

        return (
            <div>
                <ul className="nav nav-tabs">
                    <li role="presentation"
                        className={classNames({'active': this.state.currentTab === MDMarkdownEditTab})}>
                        <a href="#" onClick={e => this.openTab(e, MDMarkdownEditTab)}>Edit</a>
                    </li>
                    <li role="presentation"
                        className={classNames({'active': this.state.currentTab === MDMarkdownPreviewTab})}>
                        <a href="#" onClick={e => this.openTab(e, MDMarkdownPreviewTab)}>Preview</a>
                    </li>
                </ul>
                {this.state.currentTab === MDMarkdownEditTab ? (
                    <textarea className="form-control"
                          name={this.props.componentName}
                          rows={this.props.rows}
                          value={this.state.value}
                          onChange={this.onValueChange.bind(this)}/>
                ) : (
                    html ? html : (
                        <div>Interpreting ...</div>
                    )
                )}
            </div>
        );
    }
}

EDMarkdownEditor.defaultProps = {
    rows: 15,
    componentName: 'markdownBody'
};

export default EDMarkdownEditor;