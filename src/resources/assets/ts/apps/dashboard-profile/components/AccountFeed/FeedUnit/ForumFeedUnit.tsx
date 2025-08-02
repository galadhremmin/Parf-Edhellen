import HtmlInject from "@root/components/HtmlInject";
import { IForumFeedRecord } from "@root/connectors/backend/IAccountApi";
import { IProps } from "./index._types";

export default function ForumFeedUnit(props: IProps<IForumFeedRecord>) {
    const {
        forumThread,
        content,
        id,
    } = props.unit.content;

    return <>
        <a href={`/api/v3/discuss/thread/resolve/forum/${id}`} target="_blank" rel="noreferrer">
            <h3>{forumThread?.subject || 'unknown thread'}</h3>
        </a>
        <HtmlInject html={content} />
    </>;
}
