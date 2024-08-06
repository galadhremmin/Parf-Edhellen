import DateLabel from "@root/components/DateLabel";
import TextIcon from "@root/components/TextIcon";

export default function FeedUnit({ unit }: any) {
    return <li>
        <div className="timeline-badge info">
            <TextIcon icon="comment" className="bg-white" />
        </div>
        <div className="timeline-panel">
            <div className="timeline-heading">
                <h4 className="timeline-title">{unit.contentType}</h4>
                <p>
                    <small className="text-muted">
                        <DateLabel dateTime={unit.happenedAt} />
                    </small>
                </p>
            </div>
            <div className="timeline-body">
                {JSON.stringify(unit.content, undefined, 2)}
                <hr />
                <div className="">
                    <a href="http://localhost:8000/discuss/7-g/974-is_this_correct_sindarin_translation_for_word_brave_or_courage_-need_it_for_tattoo-?forum_post_id=3622" className="btn btn-sm btn-secondary">
                        View thread
                    </a>
                </div>
            </div>
        </div>
    </li>;
}
