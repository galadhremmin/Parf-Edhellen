import DateLabel from "@root/components/DateLabel";
import Panel from "@root/components/Panel";
import { PanelType } from "@root/components/Panel._types";
import type { IForumFeedRecord, ILexicalEntryVersionFeedRecord, ISentenceFeedRecord } from "@root/connectors/backend/IAccountApi";
import classNames from "classnames";
import { useEffect, useRef, useState } from "react";
import ForumFeedUnit from "./ForumFeedUnit";
import GlossVersionFeedUnit from "./GlossVersionFeedUnit";
import type { IProps } from './index._types';
import SentenceFeedUnit from "./SentenceFeedUnit";

export default function FeedUnit(props: IProps<IForumFeedRecord | ILexicalEntryVersionFeedRecord | ISentenceFeedRecord>) {
    const {
        first,
        unit,
    } = props;

    const componentRef = useRef();

    const [ visible, setVisible ] = useState<boolean>(false);

    useEffect(() => {
        const options = {
            rootMargin: '0px',
            threshold: 0.5,
        };

        const observer = new IntersectionObserver((entries: IntersectionObserverEntry[]) => {
            entries.forEach(entry => {
                setVisible(entry.isIntersecting);
            })
        }, options);

        if (componentRef.current) {
            observer.observe(componentRef.current);
        }

        return () => {
            if (componentRef.current) {
                observer.unobserve(componentRef.current);
            }
        };
    }, [ componentRef ]);

    return <div ref={componentRef} className="d-flex flex-row justify-content-start align-items-center align-items-stretch">
        <div className={classNames('timeline--line', {'first': first})}>
            <span></span>
        </div>
        <Panel type={PanelType.Info} className="flex-fill" shadow>
            {unit.contentType === 'forum'         && <ForumFeedUnit unit={unit} visible={visible} />}
            {unit.contentType === 'lex_entry_ver' && <GlossVersionFeedUnit unit={unit} visible={visible} />}
            {unit.contentType === 'sentence'      && <SentenceFeedUnit unit={unit} visible={visible} />}
            <div className="text-end text-secondary">
                <DateLabel dateTime={unit.happenedAt} />
            </div>
        </Panel>
    </div>;
}
