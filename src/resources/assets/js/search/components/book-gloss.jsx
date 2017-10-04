import React from 'react';
import classNames from 'classnames';
import { Parser as HtmlToReactParser, ProcessNodeDefinitions } from 'html-to-react';
import EDConfig from 'ed-config';

/**
 * Represents a single gloss. A gloss is also called a 'translation' and is reserved for invented languages.
 */
class EDBookGloss extends React.Component {
    constructor(props, context) {
        super(props, context);

        this.state = {
            isDeleted: false
        };
    }
    
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

        if (this.state.isDeleted) {
            return <div />;
        }

        return this.renderGloss(gloss);
    }

    renderGloss(gloss) {
        const id = `translation-block-${gloss.id}`;

        let comments = null;
        if (gloss.comments) {
            comments = this.processHtml(gloss.comments);
        }

        const toolbarPlugins = EDConfig.pluginsFor('book-gloss-toolbar');

        return <blockquote itemScope="itemscope" itemType="http://schema.org/Article" id={id} className={classNames({ 'contribution': !gloss.is_canon }, 'gloss')}>
            {! gloss.is_latest ? <p className="alert alert-danger">
                <span className="glyphicon glyphicon-warning-sign"></span> <strong>Important!</strong> A newer version of this gloss was found in the dictionary.
                You should <a href={`/wt/${gloss.id}/latest`}> go to the latest version instead</a>.    
            </p> : undefined}
            <h3 rel="trans-word" className="trans-word">
                {! gloss.is_canon || gloss.is_uncertain
                    ? <span className="glyphicon glyphicon-asterisk" title="Uncertain or possibly a neologism" />
                    : undefined}
                {' '}
                <span itemProp="headline" className={classNames({'rejected': gloss.is_rejected})}>
                    {gloss.word}
                </span>
                {! this.props.disableTools ? <a href={`/wt/${gloss.id}/versions`} className="ed-comments-no" title="See all versions and read comments">
                    <span className="glyphicon glyphicon-comment" />{' '}
                    <span className="no">{gloss.comment_count}</span>
                </a> : undefined}
                {! this.props.disableTools ? <a href={`/wt/${gloss.id}`} className="translation-link">
                    <span className="glyphicon glyphicon-share"></span>
                </a> : undefined}
                {! this.props.disableTools && gloss.is_latest ? toolbarPlugins.map((PluginComponent, i) => <span key={`plugin.${i}`}>
                    <PluginComponent hostComponent={this} gloss={gloss} />
                    {' '}    
                </span>) : undefined}
            </h3>
            <p>
                {gloss.tengwar ? <span className="tengwar">{gloss.tengwar}</span> : undefined}
                {' '}
                {gloss.type ? <span className="word-type" rel="trans-type">{gloss.type}.</span> : undefined}
                {' '}
                <span rel="trans-translation" itemProp="keywords">{gloss.translation}</span>
            </p>

            {comments}

            {gloss.inflections ?
            <div>
                <table className="table table-striped table-hover table-condensed">
                    <caption>Inflections</caption>
                    <thead>
                        <tr>
                            <th>Word</th>
                            <th>Inflection</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        {Object.keys(gloss.inflections).map(sentenceId => {
                            const inflections = gloss.inflections[sentenceId];
                            const firstInflection = inflections[0];

                            return <tr key={sentenceId}>
                                <td>{firstInflection.word}</td>
                                <td>
                                    <em>{firstInflection.speech}</em>
                                    {inflections.filter(inf => !! inf.inflection).map(
                                        (inf, i) => <span key={`${sentenceId}-${i}`}>
                                            {` ${inf.inflection}`}
                                        </span>
                                    )}
                                </td>
                                <td>
                                    <a href={firstInflection.sentence_url} title={`Go to ${firstInflection.sentence_name}.`}>
                                        {firstInflection.sentence_name}
                                    </a>
                                </td>
                            </tr>;
                        })}
                    </tbody>
                </table>
            </div> : ''}

            <footer>
                {gloss.source ? <span className="word-source" rel="trans-source">[{gloss.source}]</span> : ''}
                {' '}
                {gloss.etymology ?
                    <span className="word-etymology" rel="trans-etymology">{gloss.etymology}.</span> : ''}
                {' '}
                {gloss.external_link_format && gloss.external_id ?
                    <span>
                        <a href={gloss.external_link_format.replace(/\{ExternalID\}/g, gloss.external_id)}
                            title={`Goes to ${gloss.translation_group_name} in new tab or window.`}
                            target="_blank">
                            <span className="glyphicon glyphicon-globe" />
                            {' '}
                            Source
                        </a>.
                    </span>
                    : ''}
                {' '}
                {gloss.translation_group_id ?
                    (<span>Group: <span itemProp="sourceOrganization">{gloss.translation_group_name}.</span></span>) : ''}
                {' Published: '}
                <span itemProp="datePublished">{new Date(gloss.created_at).toLocaleString()}</span>
                {' by '}
                <a href={gloss.account_url} itemProp="author" rel="author" title={`View profile for ${gloss.account_name}.`}>
                    {gloss.account_name}
                </a>.
            </footer>
        </blockquote>;
    }
}

EDBookGloss.defaultProps = {
    disableTools: false
};

export default EDBookGloss;
