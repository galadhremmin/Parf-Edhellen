import React from 'react';

import { ISentenceFragmentEntity } from '@root/connectors/backend/BookApiConnector._types';
import { IProps } from './FragmentInspector._types';

export default class FragmentInspector extends React.PureComponent<IProps> {
    public render() {
        const {
            fragment,
        } = this.props;

        return <aside className="fragment-inspector">
            {fragment ? this._renderFragment(fragment) : this._renderUnknownFragment()}

            <nav aria-label="Fragment navigator">
                <ul className="pager">
                    <li className="previous"><a href="#previous">&larr; Previous</a></li>
                    <li className="next"><a href="#next">Next &rarr;</a></li>
                </ul>
            </nav>
        </aside>;
    }

    private _renderFragment(fragment: ISentenceFragmentEntity) {
        return <article>
            <header>
                <h1>{fragment.fragment}</h1>
            </header>
            <section className="abstract">
                {fragment.comments}
            </section>
        </article>
    }

    private _renderUnknownFragment() {
        return <span>Unknown fragment...</span>;
    }
}