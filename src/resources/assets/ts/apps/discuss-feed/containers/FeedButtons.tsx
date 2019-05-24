import React, {
    useCallback,
    useState,
} from 'react';

import { IComponentEvent } from '@root/components/Component._types';
import Dialog from '@root/components/Dialog';
import { LearnMoreWebFeedUrl } from '@root/config';
import { createFeedUrl } from '@root/connectors/FeedApiConnector';
import { FeedFormat } from '@root/connectors/FeedApiConnector._types';

import FeedFormatSelect from '../components/FeedFormatSelect';
import { IProps } from '../index._types';

import './FeedButtons.scss';

const onFeedUrlFocus = (ev: React.FocusEvent<HTMLInputElement>) => {
    ev.target.select();
};

function Feeds(props: IProps) {
    const {
        feedUrlFactory,
    } = props;

    const [ isOpen, setIsOpen ] = useState(false);
    const [ feedType, setFeedType ] = useState(FeedFormat.RSS);

    const _onOpen = useCallback((ev: React.MouseEvent<HTMLButtonElement>) => {
        ev.preventDefault();
        setIsOpen(true);
    }, [ setIsOpen ]);

    const _onDismiss = useCallback(() => {
        setIsOpen(false);
    }, [ setIsOpen ]);

    const _onFeedTypeChange = useCallback((ev: IComponentEvent<FeedFormat>) => {
        setFeedType(ev.value);
    }, [ setFeedType ]);

    const feedUrl = feedUrlFactory('discuss', 'posts', props.groupId, feedType);
    return <div className="discuss-feed-buttons">
        <button className="btn btn-sm btn-default" onClick={_onOpen}>
            <span className="glyphicon glyphicon-bell" />
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
                Not sure what this is? You can read about <a href={LearnMoreWebFeedUrl} target="_blank">
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
                       value={feedUrl} />
            </div>
        </Dialog>
    </div>;
}

Feeds.defaultProps = {
    feedUrlFactory: createFeedUrl,
} as Partial<IProps>;

export default Feeds;