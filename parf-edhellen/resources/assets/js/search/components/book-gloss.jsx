import React from 'react';
import classNames from 'classnames';
import { Parser as HtmlToReactParser, ProcessNodeDefinitions } from 'html-to-react';

/**
 * Represents a single gloss. A gloss is also called a 'translation' and is reserved for invented languages.
 */
class EDBookGloss extends React.Component {
    
    processHtml(html) {
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
                    const childElements = nodeElements.props.children;

                    return <a href={href}
                              onClick={ev => this.onReferenceLinkClick(ev, word)}
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

    onReferenceLinkClick(ev, word) {
        ev.preventDefault();

        if (this.props.onReferenceLinkClick) {
            this.props.onReferenceLinkClick({
                word
            });
        }
    }

    render() {
        const gloss = this.props.gloss;
        const id = `translation-block-${gloss.TranslationID}`;

        let comments = null;
        if (gloss.Comments) {
            comments = this.processHtml(gloss.Comments);
        }

        return (
            <blockquote itemScope="itemscope" itemType="http://schema.org/Article" id={id} className={classNames({ 'contribution': !gloss.Canon })}>
                <h3 rel="trans-word" className="trans-word">
                    {(!gloss.Canon || gloss.Uncertain) && gloss.Latest ?
                        <a href="/about" title="Unverified or debatable content.">
                            <span className="glyphicon glyphicon-question-sign" />
                        </a> : '' }
                    {' '}
            <span itemProp="headline">
              {gloss.Word}
            </span>
                    {gloss.ExternalLinkFormat && gloss.ExternalID ?
                        <a href={gloss.ExternalLinkFormat.replace(/\{ExternalID\}/g, gloss.ExternalID)}
                           className="ed-external-link-button"
                           title={`Open on ${gloss.TranslationGroup} (new tab/window)`}
                           target="_blank">
                            <span className="glyphicon glyphicon-globe pull-right" />
                        </a> : ''}
                </h3>
                <p>
                    {gloss.Tengwar ? <span className="tengwar">{gloss.Tengwar}</span> : ''}
                    {' '}
                    {gloss.Type != 'unset' ? <span className="word-type" rel="trans-type">{gloss.Type}.</span> : ''}
                    {' '}
                    <span rel="trans-translation" itemProp="keywords">{gloss.Translation}</span>
                </p>

                {comments}

                <footer>
                    {gloss.Source ? <span className="word-source" rel="trans-source">[{gloss.Source}]</span> : ''}
                    {' '}
                    {gloss.Etymology ?
                        <span className="word-etymology" rel="trans-etymology">{gloss.Etymology}.</span> : ''}
                    {' '}
                    {gloss.TranslationGroupID ?
                        (<span>Group: <span itemProp="sourceOrganization">{gloss.TranslationGroup}.</span></span>) : ''}
                    {' Published: '}
                    <span itemProp="datePublished">{gloss.DateCreated}</span>
                    {' by '}
                    <a href={gloss.AuthorURL} itemProp="author" rel="author" title={`View profile for ${gloss.AuthorName}.`}>
                        {gloss.AuthorName}
                    </a>
                </footer>
            </blockquote>);
    }
}

export default EDBookGloss;
