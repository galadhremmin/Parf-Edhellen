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
        const id = `translation-block-${gloss.id}`;

        let comments = null;
        if (gloss.comments) {
            comments = this.processHtml(gloss.comments);
        }

        return (
            <blockquote itemScope="itemscope" itemType="http://schema.org/Article" id={id} className={classNames({ 'contribution': !gloss.is_canon })}>
                <h3 rel="trans-word" className="trans-word">
                    {!gloss.is_canon || gloss.is_uncertain || !gloss.is_latest ?
                        <a href="/about" title="Unattested, unverified or debatable content.">
                            <span className="glyphicon glyphicon-asterisk" />
                        </a> : '' }
                    {' '}
                    <span itemProp="headline" className={classNames({'rejected': gloss.is_rejected})}>
                    {gloss.word}
                    </span>
                    {gloss.external_link_format && gloss.external_id ?
                        <a href={gloss.external_link_format.replace(/\{ExternalID\}/g, gloss.external_id)}
                            title={`Open on ${gloss.translation_group_name} (new tab/window)`}
                            target="_blank">
                            <span className="glyphicon glyphicon-globe" />
                        </a> : ''}
                    <a href={`/admin/translation/${gloss.id}/edit`} className="ed-admin-tool">
                        <span className="glyphicon glyphicon-edit" />
                    </a>
                </h3>
                <p>
                    {gloss.tengwar ? <span className="tengwar">{gloss.tengwar}</span> : ''}
                    {' '}
                    {gloss.type ? <span className="word-type" rel="trans-type">{gloss.type}.</span> : ''}
                    {' '}
                    <span rel="trans-translation" itemProp="keywords">{gloss.translation}</span>
                </p>

                {comments}

                <footer>
                    {gloss.source ? <span className="word-source" rel="trans-source">[{gloss.source}]</span> : ''}
                    {' '}
                    {gloss.etymology ?
                        <span className="word-etymology" rel="trans-etymology">{gloss.etymology}.</span> : ''}
                    {' '}
                    {gloss.translation_group_id ?
                        (<span>Group: <span itemProp="sourceOrganization">{gloss.translation_group_name}.</span></span>) : ''}
                    {' Published: '}
                    <span itemProp="datePublished">{gloss.created_at}</span>
                    {' by '}
                    <a href={gloss.account_url} itemProp="author" rel="author" title={`View profile for ${gloss.account_name}.`}>
                        {gloss.account_name}
                    </a>.
                </footer>
            </blockquote>);
    }
}

export default EDBookGloss;
