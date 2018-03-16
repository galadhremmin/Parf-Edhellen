import React from 'react';
import classNames from 'classnames';
import { Parser as HtmlToReactParser, ProcessNodeDefinitions } from 'html-to-react';
import EDAPI from 'ed-api';

/**
 * Injects HTML within a gloss context. 
 */
class EDHtmlInjection extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            reactComponents: this.processHtml(props.html)
        };
    }

    componentWillReceiveProps(props) {
        if (props.html) {
            const reactComponents = this.processHtml(props.html);
            this.setState({
                reactComponents
            });
        }
    }
    
    processHtml(html) {
        if (! html || /^\s*$/.test(html)) {
            return null;
        }

        const definitions = new ProcessNodeDefinitions(React);
        const instructions = [
            // Special behaviour for <a> as they are reference links.
            {
                shouldProcessNode: node => node.name === 'a',
                processNode: (node, children) => {
                    const nodeElements = definitions.processDefaultNode(node, children);
                    if (node.attribs.class !== 'ed-word-reference') {
                        return nodeElements;
                    }

                    // Replace reference links with a link that is aware of
                    // the component, and can intercept click attempts.
                    const href = node.attribs.href;
                    const title = node.attribs.title;
                    const word = node.attribs['data-word'];
                    const languageShortName = node.attribs['data-language-short-name'];
                    const childElements = nodeElements.props.children;

                    return <a href={href}
                              onClick={ev => this.onReferenceLinkClick(ev, word, languageShortName)}
                              title={title}>{childElements}</a>;
                }
            },
            // Default behaviour for all else.
            {
                shouldProcessNode: node => true,
                processNode: definitions.processDefaultNode
            }];

        const parser = new HtmlToReactParser();
        return parser.parseWithInstructions(html, n => true, instructions);
    }

    onReferenceLinkClick(ev, word, languageShortName) {
        ev.preventDefault();

        if (this.props.onReferenceLinkClick) {
            EDAPI.languages(languageShortName || null, 'short_name').then(language => {
                this.props.onReferenceLinkClick({
                    word,
                    language
                });
            });
        }
    }

    render() {
        const components = this.state.reactComponents;
        return components || null;
    }
}

EDHtmlInjection.defaultProps = {
    html: null,
    onReferenceLinkClick: null
}

export default EDHtmlInjection;
