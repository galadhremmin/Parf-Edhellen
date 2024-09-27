import HtmlInject from "@root/components/HtmlInject";
import Tengwar from "@root/components/Tengwar";
import TextIcon from "@root/components/TextIcon";
import { ISentenceFeedRecord } from "@root/connectors/backend/IAccountApi";
import { IProps } from "./index._types";

export default function SentenceFeedUnit(props: IProps<ISentenceFeedRecord>) {
    const {
        unit,
    } = props;
    const {
        name,
        description,
        sentenceFragments,
        sentenceTransformations,
        sentenceUrl,
    } = unit.content;

    /**
     * The sentence data model is a little complicated to parse out due to various writing systems.
     * Each writing system creates a transformation. Each transformation generates fragments which are keyed by paragraph.
     * A fragment is either an index reference to one of the paragraphs, or a raw string for things like interpunctuation.
     * 
     * Thus the data model is:
     * transformation --> paragraph index --> ( [ fragment index, transcription string ] OR string )
     * 
     * The variables below parses out the transformations (we don't want to assume which ones we have available) and also
     * determines the paragraph indexes. These indexes should be the same for all transformations.
     */
    const transformations = Object.keys(sentenceTransformations);
    const paragraphIndexes = Object.keys(transformations[0] || {});

    return <>
        <p>
            <TextIcon icon="book" />{' '}
            Published the phrase <em>{name}</em>. <a href={sentenceUrl} target="_blank">Open in the viewer</a>.
        </p>
        {transformations.length > 0 && <>
            <hr />
            <h3>{name}</h3>
            <HtmlInject html={description} />
            {paragraphIndexes.map(paragraphIndex => <p key={paragraphIndex}>
                {transformations.map(transformation => <div key={transformation}>
                    {sentenceTransformations[transformation][paragraphIndex]?.map(fragment => <span>
                        {Array.isArray(fragment) //
                            ? (fragment.length > 1 //
                                    ? <Tengwar transcribe={false} text={fragment[1]} />
                                    : <em>{sentenceFragments[fragment[0]]?.fragment}</em>
                            ) //
                            : fragment
                        }
                    </span>)}
                </div>)}
            </p>)}
        </>}
    </>;
}
