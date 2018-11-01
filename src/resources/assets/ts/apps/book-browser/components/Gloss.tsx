import classNames from 'classnames';
import React from 'react';

import { IGlossEntity } from '../../../connectors/backend/BookApiConnector._types';
import { IProps } from './Gloss._types';

import Tengwar from '../../../components/Tengwar';
import GlossAbsoluteLink from './GlossAbsoluteLink';
import GlossTitle from './GlossTitle';
import NumberOfComments from './NumberOfComments';
import OldVersionAlert from './OldVersionAlert';

export default class Gloss extends React.PureComponent<IProps> {
    public render() {
        const { gloss } = this.props;

        const id = `gloss-block-${gloss.id}`;
        const className = classNames({ 'contribution': !gloss.isCanon }, 'gloss');
        // const toolbarPlugins = EDConfig.pluginsFor('book-gloss-toolbar');

        return <blockquote itemScope={true} itemType="http://schema.org/Article" 
            id={id} className={className}>
            <OldVersionAlert gloss={gloss} />
            <h3 className="gloss-word">
                <GlossTitle gloss={gloss} />
                <NumberOfComments gloss={gloss} />
                <GlossAbsoluteLink gloss={gloss} />
            </h3>
            <p>
                <Tengwar text={gloss.tengwar} />
                {' '}
                {gloss.type ? <span className="word-type" rel="gloss-type">{gloss.type}.</span> : undefined}
                {' '}
                <span rel="gloss-translations" itemProp="keywords">
                    {gloss.all_translations}
                </span>
            </p>

            <EDHtmlInjection html={gloss.comments} onReferenceLinkClick={this.onReferenceLinkClick.bind(this)} />
            {!this.props.disableDetails && gloss.gloss_details.map(d => <EDBookGlossDetail key={d.order} detail={d} onReferenceLinkClick={this.onReferenceLinkClick.bind(this)} />)}

            {!this.props.disableInflections && gloss.inflections ?
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
                {gloss.source ? <span className="word-source" rel="gloss-source">[{gloss.source}]</span> : ''}
                {' '}
                {gloss.etymology ?
                    <span className="word-etymology" rel="gloss-etymology">{gloss.etymology}.</span> : ''}
                {' '}
                {gloss.external_link_format && gloss.external_id ?
                    <span>
                        <a href={gloss.external_link_format.replace(/\{ExternalID\}/g, gloss.external_id)}
                            title={`Goes to ${gloss.gloss_group_name} in new tab or window.`}
                            target="_blank">
                            <span className="glyphicon glyphicon-globe" />
                            {' '}
                            Source
                        </a>.
                    </span>
                    : ''}
                {' '}
                {gloss.gloss_group_id ?
                    (<span>Group: <span itemProp="sourceOrganization">{gloss.gloss_group_name}.</span></span>) : ''}
                {' Published: '}
                <span itemProp="datePublished">{new Date(gloss.created_at).toLocaleString()}</span>
                {' by '}
                <a href={gloss.account_url} itemProp="author" rel="author" title={`View profile for ${gloss.account_name}.`}>
                    {gloss.account_name}
                </a>.
            </footer>
        </blockquote>;
    }

    _renderNotLatestVersionAlert(gloss: IGlossEntity) {
        return <p className="alert alert-danger">
            <span className="glyphicon glyphicon-warning-sign"></span> <strong>Important!</strong> A newer version of this gloss was found in the dictionary.
            You should <a href={`/wt/${gloss.id}/latest`}> go to the latest version instead</a>.
        </p>;
    }

    _renderNeologism() {
        return <span className="uncertain" title="Uncertain or possibly a neologism">*</span>;
    }
}

