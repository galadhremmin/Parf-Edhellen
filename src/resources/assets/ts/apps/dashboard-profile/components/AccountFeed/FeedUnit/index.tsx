import Panel from "@root/components/Panel";
import { PanelType } from "@root/components/Panel._types";
import { useEffect, useRef, useState } from "react";
import ForumFeedUnit from "./ForumFeedUnit";
import GlossFeedUnit from "./GlossFeedUnit";
import { IProps } from './index._types';
import SentenceFeedUnit from "./SentenceFeedUnit";

export default function FeedUnit(props: IProps) {
    const {
        unit,
    } = props;

    const componentRef = useRef();

    const [ visible, setVisible ] = useState<boolean>(false);

    useEffect(() => {
        const options = {
            rootMargin: '0px',
            threshold: 0.5,
        };

        const observer = new IntersectionObserver((entries: IntersectionObserverEntry[], observer: IntersectionObserver) => {
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

    return <div ref={componentRef}>
        <Panel type={PanelType.Info} shadow>
            {visible ? 'Visible' : 'Not visible'}
            {unit.contentType === 'forum'    ? <ForumFeedUnit {...props} /> : (
                unit.contentType === 'gloss'    ? <GlossFeedUnit {...props} /> : (
                unit.contentType === 'sentence' ? <SentenceFeedUnit {...props} /> : 
                `unknown content ${JSON.stringify(unit)}`))}    
        </Panel>
    </div>;
}
