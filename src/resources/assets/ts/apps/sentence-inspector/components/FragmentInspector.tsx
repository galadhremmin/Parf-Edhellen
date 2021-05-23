import React, { useCallback, useEffect, useRef } from 'react';

import StaticAlert from '@root/components/StaticAlert';
import Tengwar from '@root/components/Tengwar';
import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import { IProps } from './FragmentInspector._types';

import './FragmentInspector.scss';
import TextIcon from '@root/components/TextIcon';
import GlossFragmentInspector from './GlossFragmentInspector';
import { fireEventAsync } from '@root/components/Component';
import classNames from 'classnames';

function jumpToComponent(component: HTMLElement) {
    if (component) {
        // TODO: we should not use `previousElementSibling` here as it 'leaks' out
        // of the component. The reason for this behavior is this: the inspector
        // is injected /after/ the paragraph currently selected. So the previous
        // sibling is always the selected text.
        const sibling = component.previousElementSibling;
        if (sibling) {
            // -24 is a little offset to ensure that the viewport does not scroll beyond
            // the element
            makeVisibleInViewport(sibling, [0, -24]);
        }
    }
}

export function FragmentInspector(props: IProps) {
    const _rootRef = useRef<HTMLElement>();

    /**
     * Jump to the component when it mounts, as it is expected to only be
     * mounted when the customer is interested in its content.
     */
    useEffect(() => {
        jumpToComponent(_rootRef.current);
        document.body.classList.add('fragment-inspector--open');
        return () => {
            document.body.classList.remove('fragment-inspector--open');
        }
    }, []);

    /**
     * Jump to the component when it re-renders, as the customer is expecting
     * to see its content.
     */
    useEffect(() => {
        jumpToComponent(_rootRef.current);
    });

    const {
        fragment,
        onNextOrPreviousFragmentClick,
        onSelectFragment,
    } = props;
    const {
        previousFragmentId,
        nextFragmentId,
    } = props.fragment;

    const _onCloseClick = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        fireEventAsync('FragmentInspector', onSelectFragment, null);
    }, [ onSelectFragment ]);

    const _onPreviousClick = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        if (previousFragmentId) {
            fireEventAsync('FragmentInspector', onNextOrPreviousFragmentClick, previousFragmentId);
        }
    }, [ onNextOrPreviousFragmentClick, previousFragmentId ]);

    const _onNextClick = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();
        if (nextFragmentId) {
            fireEventAsync('FragmentInspector', onNextOrPreviousFragmentClick, nextFragmentId);
        }
    }, [ onNextOrPreviousFragmentClick, nextFragmentId ]);

    return <aside className="fragment-inspector" ref={_rootRef}>
        <nav aria-label="Fragment navigator">
            <ul className="pager">
                <li className="previous">
                    <a href="#previous"
                        className={classNames({ disabled: ! previousFragmentId})}
                        onClick={_onPreviousClick}>
                        <TextIcon icon="chevron-left" />
                    </a>
                    <a href="#"
                        onClick={_onCloseClick}>
                        <TextIcon icon="remove" />
                    </a>
                </li>
                {fragment && <li className="tengwar-pill">
                    <Tengwar text={fragment.tengwar} transcribe={false} />
                </li>}
                <li className="next">
                    <a href="#next"
                        onClick={_onNextClick}
                        className={classNames({ disabled: ! nextFragmentId})}>
                        <TextIcon icon="chevron-right" />
                    </a>
                </li>
            </ul>
        </nav>
        {! fragment && <StaticAlert type="warning">
            <strong>The word does not exist!</strong>{' '}
            The word has probably been removed from the dictionary. We have recorded this error. Sorry!
        </StaticAlert>}
        {fragment && <GlossFragmentInspector fragment={fragment} />}
    </aside>;
}

export default FragmentInspector;

