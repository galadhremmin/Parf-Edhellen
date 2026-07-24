import classNames from '@root/utilities/ClassNames';
import HtmlInject from '@root/components/HtmlInject';
import type { IProps } from './LexicalEntry._types';

import LexicalEntryDetails from './LexicalEntryDetails';
import LexicalEntryFooter from './LexicalEntryFooter';
import WordInflections from './WordInflections';
import LexicalEntryTitle from './LexicalEntryTitle';
import GlossList from './GlossList';
import OldVersionAlert from './OldVersionAlert';

import './LexicalEntry.scss';
import LexicalEntryGroupLabel from './LexicalEntryGroupLabel';

function LexicalEntry(props: IProps) {
    const {
        bordered = true,
        demoted = false,
        featured = false,
        lexicalEntry,
        onPromoteFeatured,
        onReferenceLinkClick,
        toolbar = true,
        warnings = true,
    } = props;

    const id = `lexical-entry-block-${lexicalEntry.id}`;
    const className = classNames({
        contribution: !lexicalEntry.isCanon,
        'shadow-sm': bordered,
        border: bordered,
        rounded: bordered,
        'lexical-entry--featured': featured,
        'lexical-entry--demoted': demoted,
    }, 'lexical-entry');

    return <blockquote itemScope={true} itemType="http://schema.org/Article" id={id} className={className}>
        {warnings && <OldVersionAlert lexicalEntry={lexicalEntry} />}
        <LexicalEntryGroupLabel lexicalEntry={lexicalEntry} featured={featured} />
        <LexicalEntryTitle lexicalEntry={lexicalEntry} toolbar={toolbar} onPromoteFeatured={onPromoteFeatured} />
        <GlossList lexicalEntry={lexicalEntry} />
        <div className="lexical-entry__summary">
            <HtmlInject html={lexicalEntry.comments} onReferenceLinkClick={onReferenceLinkClick} />
        </div>
        <div className="lexical-entry__body">
            <LexicalEntryDetails lexicalEntry={lexicalEntry} showDetails={true} onReferenceLinkClick={onReferenceLinkClick} />
            <WordInflections lexicalEntry={lexicalEntry} />
        </div>
        <LexicalEntryFooter lexicalEntry={lexicalEntry} />
    </blockquote>;
}

export default LexicalEntry;
