import LexicalEntry from '@root/apps/book-browser/components/GlossaryEntities/LexicalEntry';
import Spinner from '@root/components/Spinner';
import TextIcon from '@root/components/TextIcon';
import type { ILexicalEntryVersionFeedRecord } from "@root/connectors/backend/IAccountApi";
import useLexicalEntry from "@root/utilities/hooks/useLexicalEntry";
import type { IProps } from "./index._types";

export default function GlossVersionFeedUnit(props: IProps<ILexicalEntryVersionFeedRecord>) {
    const {
        unit,
        visible = false,
    } = props;

    const { lexicalEntry: gloss } = useLexicalEntry(unit.contentId, {
        isEnabled: visible,
        isVersion: true,
    });

    if (! gloss) {
        return <Spinner />;
    }

    return <>
        <p>
            <TextIcon icon="book" />{' '}
            Published a gloss revision for {unit.content.lexicalEntryId}. <a href={`/wt/${unit.content.lexicalEntryId}`} target="_blank" rel="noreferrer">Open the current version in the dictionary</a>.
        </p>
        <hr className="mb-0" />
        <LexicalEntry lexicalEntry={gloss} bordered={false} toolbar={false} />
    </>;
}
