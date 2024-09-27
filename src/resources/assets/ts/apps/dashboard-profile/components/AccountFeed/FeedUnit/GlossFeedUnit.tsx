import Gloss from '@root/apps/book-browser/components/GlossaryEntities/Gloss';
import Spinner from '@root/components/Spinner';
import TextIcon from '@root/components/TextIcon';
import { IGlossFeedRecord } from "@root/connectors/backend/IAccountApi";
import useGloss from "@root/utilities/hooks/useGloss";
import { IProps } from "./index._types";

export default function GlossFeedUnit(props: IProps<IGlossFeedRecord>) {
    const {
        unit,
        visible = false,
    } = props;

    const { gloss } = useGloss(unit.contentId, {
        isEnabled: visible,
    });

    if (! gloss) {
        return <Spinner />;
    }

    return <>
        <p>
            <TextIcon icon="book" />{' '}
            Published the gloss {unit.contentId}. <a href={`/wt/${unit.contentId}`} target="_blank" rel="noreferrer">Open in the dictionary</a>.
        </p>
        <hr className="mb-0" />
        <Gloss gloss={gloss} bordered={false} toolbar={false} />
    </>;
}
