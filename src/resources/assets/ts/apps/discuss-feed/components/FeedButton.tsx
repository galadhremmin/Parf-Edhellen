import {
    useCallback,
    useState,
} from 'react';
import type { FocusEvent, MouseEvent } from 'react';

import { LearnMoreWebFeedUrl } from '@root/config';
import type { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import TextIcon from '@root/components/TextIcon';
import { FeedFormat } from '@root/connectors/IFeedApi';
import FeedFormatSelect from './FeedFormatSelect';
import type { IProps } from './FeedButton._types';

const onFeedUrlFocus = (ev: FocusEvent<HTMLInputElement>) => {
    ev.target.select();
};

function FeedButton({
    feedUrlFactory,
    groupId,
}: IProps) {

    const [ isOpen, setIsOpen ] = useState(false);
    const [ feedType, setFeedType ] = useState(FeedFormat.RSS);

    const _onOpen = useCallback((ev: MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        setIsOpen(true);
    }, [ setIsOpen ]);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const _onFeedTypeChange = useCallback((ev: IComponentEvent<FeedFormat>) => {
        setFeedType(ev.value);
    }, [ setFeedType ]);

    const feedUrl = feedUrlFactory('discuss', 'posts', groupId, feedType);
    return <>
        <button className="btn btn-sm btn-secondary" onClick={_onOpen}>
            <TextIcon icon="rss" />
            &#32;
            Feed
        </button>
        <Dialog<void> onDismiss={_onDismiss}
                      open={isOpen}
                      title="Subscribe to feed">
            <p>
                Paste the feed address below into your feed reader of choice to register to this web feed.
            </p>
            <p>
                Not sure what this is? You can read about <a href={LearnMoreWebFeedUrl} target="_blank" rel="noreferrer">
                    web feeds on Wikipedia
                </a>.
            </p>
            <div className="form-group">
                <label htmlFor="feed-type">Feed format</label>
                <FeedFormatSelect id="feed-type"
                                  onChange={_onFeedTypeChange}
                                  value={feedType} />
            </div>
            <div className="form-group">
                <label htmlFor="feed-url">Feed address</label>
                <input className="form-control"
                       id="feed-url"
                       onFocus={onFeedUrlFocus}
                       type="text"
                       readOnly={true}
                       value={feedUrl} />
                <div className="text-end">
                    <a href={feedUrl} target="_blank" rel="noreferrer">Open feed in a new tab</a>
                </div>
            </div>
        </Dialog>
    </>;
}

export default FeedButton;
