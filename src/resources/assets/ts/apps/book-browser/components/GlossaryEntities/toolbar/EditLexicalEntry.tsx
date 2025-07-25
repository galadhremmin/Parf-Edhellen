import TextIcon from '@root/components/TextIcon';
import { IProps } from './index._types';

function EditLexicalEntry(props: IProps) {
    const {
        lexicalEntry: entry,
    } = props;

    return <>
        <a href={`/contribute/contribution/create/lexical_entry?entity_id=${entry.id}`}>
            <TextIcon icon="edit" />
        </a>
    </>;
}

export default EditLexicalEntry;
