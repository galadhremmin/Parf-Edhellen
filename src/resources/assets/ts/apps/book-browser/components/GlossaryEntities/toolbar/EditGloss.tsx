import TextIcon from '@root/components/TextIcon';
import { IProps } from './index._types';

function EditGloss(props: IProps) {
    const {
        gloss,
    } = props;

    return <>
        <a href={`/contribute/contribution/create/gloss?entity_id=${gloss.id}`}>
            <TextIcon icon="edit" />
        </a>
    </>;
}

export default EditGloss;
