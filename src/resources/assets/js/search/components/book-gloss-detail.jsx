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

    render() {
        if (this.props.detail === null) {
            return null;
        }

        return <section className="details">
            <header>
                <h4>{this.props.detail.category}</h4>
            </header>
            <div>
                <EDHtmlInjection html={this.props.detail.text} />
            </div>
        </section>;
    }
}

EDBookGlossDetail.defaultProps = {
    detail: null
}

export default EDBookGlossDetail;
