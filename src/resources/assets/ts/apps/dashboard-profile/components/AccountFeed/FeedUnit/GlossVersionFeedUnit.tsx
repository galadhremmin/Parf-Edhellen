import Gloss from '@root/apps/book-browser/components/GlossaryEntities/Gloss';
import Spinner from '@root/components/Spinner';
import TextIcon from '@root/components/TextIcon';
import { IGlossVersionFeedRecord } from "@root/connectors/backend/IAccountApi";
import useGloss from "@root/utilities/hooks/useGloss";
import { IProps } from "./index._types";

export default function GlossVersionFeedUnit(props: IProps<IGlossVersionFeedRecord>) {
    const {
        unit,
        visible = false,
    } = props;

    const { gloss } = useGloss(unit.contentId, {
        isEnabled: visible,
        isVersion: true,
    });

    if (! gloss) {
        return <Spinner />;
    }

    return <>
        <p>
            <TextIcon icon="book" />{' '}
            Published a gloss revision for {unit.content.glossId}. <a href={`/wt/${unit.content.glossId}`} target="_blank" rel="noreferrer">Open the current version in the dictionary</a>.
        </p>
        <hr className="mb-0" />
        <Gloss gloss={gloss} bordered={false} toolbar={false} />
    </>;
}
