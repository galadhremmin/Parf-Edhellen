import { IForumFeedRecord } from "@root/connectors/backend/IAccountApi";
import { IProps } from "./index._types";

export default function ForumFeedUnit(props: IProps<IForumFeedRecord>) {
    const {
        forumThread,
        content,
    } = props.unit.content;

    return <>
        <h2>{forumThread.subject}</h2>
    </>;
}
