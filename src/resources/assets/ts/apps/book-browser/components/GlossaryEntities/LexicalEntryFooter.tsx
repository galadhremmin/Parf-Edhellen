import DateLabel from '@root/components/DateLabel';
import TextIcon from '@root/components/TextIcon';
import { IProps } from './LexicalEntryFooter._types';

const LexicalEntryFooter = (props: IProps) => {
    const { lexicalEntry: entry } = props;

    return <footer>
        {entry.source && <span className="word-source">[{entry.source}]</span>}
        {' '}
        {entry.etymology && <span className="word-etymology">{entry.etymology}.</span>}
        {' '}
        {entry.externalLinkFormat && entry.externalId && <>
            <a href={entry.externalLinkFormat.replace(/\{ExternalID\}/g, entry.externalId)}
                title={`Opens ${entry.lexicalEntryGroupName} in new tab or window.`}
                target="_blank"
                rel="noreferrer">
                <TextIcon icon="globe" />
                {' '}
                External source
            </a>
        </>
        }
        {' '}
        {entry.lexicalEntryGroupId && <>
            Group: <span itemProp="sourceOrganization">{entry.lexicalEntryGroupName}.</span>
        </>}
        {' Published '}
        <span itemProp="datePublished">
            <DateLabel dateTime={entry.createdAt} />
        </span>
        {(entry.updatedAt && entry.updatedAt !== entry.createdAt) && <>
            {' and modified '}
            <span itemProp="dateModified">
                <DateLabel dateTime={entry.updatedAt} />
            </span>
        </>}
        {' by '}
        <a href={entry.accountUrl} itemProp="author" rel="author" title={`View profile for ${entry.accountName}.`}>
            {entry.accountName}
        </a>.
    </footer>;
};

export default LexicalEntryFooter;
