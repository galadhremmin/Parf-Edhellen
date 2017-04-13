import React from 'react';
import classNames from 'classnames';
import { Parser as HtmlToReactParser } from 'html-to-react';

/**
 * Represents a single gloss. A gloss is also called a 'translation' and is reserved for invented languages.
 */
class EDBookGloss extends React.Component {
    render() {
        const gloss = this.props.gloss;
        const id = `translation-block-${gloss.TranslationID}`;

        let comments = null;
        if (gloss.Comments) {
            const parser = new HtmlToReactParser();
            comments = parser.parse(gloss.Comments);
        }

        return (
            <blockquote itemscope="itemscope" itemtype="http://schema.org/Article" id={id} className={classNames({ 'contribution': !gloss.Canon })}>
                <h3 rel="trans-word" className="trans-word">
                    {(!gloss.Canon || gloss.Uncertain) && gloss.Latest ?
                        <a href="/about" title="Unverified or debatable content.">
                            <span className="glyphicon glyphicon-question-sign" />
                        </a> : '' }
                    {' '}
            <span itemprop="headline">
              {gloss.Word}
            </span>
                    {gloss.ExternalLinkFormat && gloss.ExternalID ?
                        <a href={gloss.ExternalLinkFormat.replace(/\{ExternalID\}/g, gloss.ExternalID)}
                           className="ed-external-link-button"
                           title={`Open on ${gloss.TranslationGroup} (new tab/window)`}
                           target="_blank">
                            <span class="glyphicon glyphicon-globe pull-right" />
                        </a> : ''}
                </h3>
                <p>
                    {gloss.Tengwar ? <span className="tengwar">{gloss.Tengwar}</span> : ''}
                    {' '}
                    {gloss.Type != 'unset' ? <span className="word-type" rel="trans-type">{gloss.Type}.</span> : ''}
                    {' '}
                    <span rel="trans-translation" itemprop="keywords">{gloss.Translation}</span>
                </p>

                {comments}

                <footer>
                    {gloss.Source ? <span className="word-source" rel="trans-source">[{gloss.Source}]</span> : ''}
                    {' '}
                    {gloss.Etymology ?
                        <span className="word-etymology" rel="trans-etymology">{gloss.Etymology}.</span> : ''}
                    {' '}
                    {gloss.TranslationGroupID ?
                        (<span>Group: <span itemprop="sourceOrganization">{gloss.TranslationGroup}.</span></span>) : ''}
                    {' Published: '}
                    <span itemprop="datePublished">{gloss.DateCreated}</span>
                    {' by '}
                    <a href={gloss.AuthorURL} itemprop="author" rel="author" title={`View profile for ${gloss.AuthorName}.`}>
                        {gloss.AuthorName}
                    </a>
                </footer>
            </blockquote>);
    }
}

export default EDBookGloss;
