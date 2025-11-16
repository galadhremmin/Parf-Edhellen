import classNames from '@root/utilities/ClassNames';
import type { IProps } from './LexicalEntry._types';

import LexicalEntryDetails from './LexicalEntryDetails';
import LexicalEntryFooter from './LexicalEntryFooter';
import WordInflections from './WordInflections';
import LexicalEntryTitle from './LexicalEntryTitle';
import GlossList from './GlossList';
import OldVersionAlert from './OldVersionAlert';

import './LexicalEntry.scss';

function LexicalEntry(props: IProps) {
    const {
        bordered = true,
        lexicalEntry,
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
    }, 'lexical-entry');

    return <blockquote itemScope={true} itemType="http://schema.org/Article" id={id} className={className}>
        {warnings && <OldVersionAlert lexicalEntry={lexicalEntry} />}
        <LexicalEntryTitle lexicalEntry={lexicalEntry} toolbar={toolbar} />
        <GlossList lexicalEntry={lexicalEntry} />
        <LexicalEntryDetails lexicalEntry={lexicalEntry} showDetails={true} onReferenceLinkClick={onReferenceLinkClick} />
        <WordInflections lexicalEntry={lexicalEntry} />
        <LexicalEntryFooter lexicalEntry={lexicalEntry} />
    </blockquote>;
}

export default LexicalEntry;
