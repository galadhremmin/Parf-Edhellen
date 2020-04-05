import classNames from 'classnames';
import React, { Suspense } from 'react';

import { fireEvent } from '@root/components/Component';
import { IComponentEvent } from '@root/components/Component._types';
import { IReferenceLinkClickDetails } from '@root/components/HtmlInject._types';
import Markdown from '@root/components/Markdown';
import Spinner from '@root/components/Spinner';
import StaticAlert from '@root/components/StaticAlert';
import Tengwar from '@root/components/Tengwar';
import { ISentenceFragmentEntity } from '@root/connectors/backend/IBookApi';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';

import { IProps } from './FragmentInspector._types';

import './FragmentInspector.scss';
import TextIcon from '@root/components/TextIcon';

export default class FragmentInspector extends React.Component<IProps> {
    private _globalEvents = new GlobalEventConnector();
    private _rootRef = React.createRef<HTMLElement>();

    /**
     * Jump to the component when it mounts, as it is expected to only be
     * mounted when the customer is interested in its content.
     */
    public componentDidMount() {
        this._jumpToComponent();
        document.body.classList.add('fragment-inspector--open');
    }

    public componentWillUnmount() {
        document.body.classList.remove('fragment-inspector--open');
    }

    /**
     * Jump to the component when it re-renders, as the customer is expecting
     * to see its content.
     */
    public componentDidUpdate() {
        this._jumpToComponent();
    }

    public render() {
        const {
            fragment,
            gloss,
        } = this.props;

        return <aside className="fragment-inspector" ref={this._rootRef}>
            <nav aria-label="Fragment navigator">
                <ul className="pager">
                    <li className="previous">
                        <a href="#previous"
                            onClick={this._onPreviousClick}>
                            <TextIcon icon="chevron-left" />
                        </a>
                        <a href="#"
                            onClick={this._onCloseClick}>
                            <TextIcon icon="remove" />
                        </a>
                    </li>
                    {fragment && <li className="tengwar-pill">
                        <Tengwar text={fragment.tengwar} transcribe={false} />
                    </li>}
                    <li className="next">
                        <a href="#next"
                            onClick={this._onNextClick}>
                            <TextIcon icon="chevron-right" />
                        </a>
                    </li>
                </ul>
            </nav>
            {fragment && gloss ? this._renderFragment(fragment) : this._renderUnknownFragment()}
        </aside>;
    }

    private _renderFragment(fragment: ISentenceFragmentEntity) {
        return <article>
            <header>
                <h1>{fragment.fragment}</h1>
            </header>
            {fragment.comments && <section className="abstract">
                <Markdown text={fragment.comments} parse={true} />
            </section>}
            <section>
                <Suspense fallback={<Spinner />}>
                    <GlossInspectorAsync gloss={this.props.gloss}
                        onReferenceLinkClick={this._onReferenceLinkClick}
                        toolbar={false} />
                </Suspense>
            </section>
        </article>;
    }

    private _renderUnknownFragment() {
        return <StaticAlert type="warning">
            <strong>The word does not exist!</strong>{' '}
            The word has probably been removed from the dictionary. We have recorded this error. Sorry!
        </StaticAlert>;
    }

    private _onCloseClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        this._selectFragment(null);
    }

    private _onPreviousClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        const {
            previousFragmentId,
        } = this.props.fragment;
        if (previousFragmentId) {
            this._selectFragment(previousFragmentId);
        }
    }

    private _onNextClick = (ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        const {
            nextFragmentId,
        } = this.props.fragment;
        if (nextFragmentId) {
            this._selectFragment(nextFragmentId);
        }
    }

    private _onReferenceLinkClick = (ev: IComponentEvent<IReferenceLinkClickDetails>) => {
        this._globalEvents.fire(this._globalEvents.loadReference, ev.value);
    }

    private _selectFragment(id: number) {
        const {
            onFragmentMoveClick,
        } = this.props;

        if (onFragmentMoveClick) {
            fireEvent(this, onFragmentMoveClick, id);
        }
    }

    private _jumpToComponent() {
        const {
            current: component,
        } = this._rootRef;

        if (component) {
            // TODO: we should not use `previousElementSibling` here as it 'leaks' out
            // of the component. The reason for this behavior is this: the inspector
            // is injected /after/ the paragraph currently selected. So the previous
            // sibling is always the selected text.
            const sibling = component.previousElementSibling;
            if (sibling) {
                sibling.scrollIntoView({
                    block: 'start',
                });
            }
        }
    }
}

const GlossInspectorAsync = React.lazy(() => import('@root/apps/book-browser/components/Gloss'));
