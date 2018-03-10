import React from 'react';
import classNames from 'classnames';
import { Parser as HtmlToReactParser, ProcessNodeDefinitions } from 'html-to-react';
import EDHtmlInjection from './html-injection';

/**
 * Represents a single gloss detail. 
 */
class EDBookGlossDetail extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            open: true
        };
    }

    onReferenceLinkClick(ev) {
        if (this.props.onReferenceLinkClick) {
            this.props.onReferenceLinkClick(ev);
        }
    }

    render() {
        if (this.props.detail === null) {
            return null;
        }

        return <section className="details">
            <header>
                <h4>{this.props.detail.category}</h4>
            </header>
            <div>
                <EDHtmlInjection html={this.props.detail.text} onReferenceLinkClick={this.onReferenceLinkClick.bind(this)} />
            </div>
        </section>;
    }
}

EDBookGlossDetail.defaultProps = {
    detail: null
}

export default EDBookGlossDetail;
