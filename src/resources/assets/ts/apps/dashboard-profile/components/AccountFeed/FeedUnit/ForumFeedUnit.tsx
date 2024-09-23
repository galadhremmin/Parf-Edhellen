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
        <a href={`/api/v2/discuss/thread/resolve/forum/${id}`}>
            <h3>{forumThread?.subject || 'unknown thread'}</h3>
        </a>
        <HtmlInject html={content} />
    </>;
}
