import classNames from 'classnames';
import React from 'react';

import HtmlInject from '@root/components/HtmlInject';
import { IProps } from './GlossDetail._types';

function GlossDetail(props: IProps) {
    const {
        detail: d,
        onReferenceLinkClick,
    } = props;

    return <section className="GlossDetails details">
        <header>
            <h4>{d.category}</h4>
        </header>
        <div className={classNames('details__body', { [String(d.type)]: !!d.type })}>
            <HtmlInject html={d.text} onReferenceLinkClick={onReferenceLinkClick} />
        </div>
    </section>;
}

export default GlossDetail;
