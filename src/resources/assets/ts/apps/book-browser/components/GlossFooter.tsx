import React from 'react';

import DateLabel from '@root/components/DateLabel';
import TextIcon from '@root/components/TextIcon';
import { IProps } from './GlossFooter._types';

const GlossFooter = (props: IProps) => {
    const { gloss } = props;

    return <footer>
        {gloss.source && <span className="word-source">[{gloss.source}]</span>}
        {' '}
        {gloss.etymology && <span className="word-etymology">{gloss.etymology}.</span>}
        {' '}
        {gloss.externalLinkFormat && gloss.externalId && <React.Fragment>
            <a href={gloss.externalLinkFormat.replace(/\{ExternalID\}/g, gloss.externalId)}
                title={`Goes to ${gloss.glossGroupName} in new tab or window.`}
                target="_blank">
                <TextIcon icon="globe" />
                {' '}
                Source
            </a>.
        </React.Fragment>
        }
        {' '}
        {gloss.glossGroupId && <React.Fragment>
            Group: <span itemProp="sourceOrganization">{gloss.glossGroupName}.</span>
        </React.Fragment>}
        {' Published: '}
        <span itemProp="datePublished">
            <DateLabel dateTime={gloss.createdAt} />
        </span>
        {' by '}
        <a href={gloss.accountUrl} itemProp="author" rel="author" title={`View profile for ${gloss.accountName}.`}>
            {gloss.accountName}
        </a>.
    </footer>;
};

export default GlossFooter;
