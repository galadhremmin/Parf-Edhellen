import { useCallback, useEffect, useRef } from 'react';
import type { MouseEvent } from 'react';

import { makeVisibleInViewport } from '@root/utilities/func/visual-focus';

import type { IProps } from './FragmentInspector._types';

import { fireEventAsync } from '@root/components/Component';
import TextIcon from '@root/components/TextIcon';
import classNames from 'classnames';
import './FragmentInspector.scss';
import SentenceFragmentInspector from './SentenceFragmentInspector';

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

    const {
        fragment,
        onNextOrPreviousFragmentClick,
        onSelectFragment,
    } = props;
    const {
        previousFragmentId,
        nextFragmentId,
    } = props.fragment;

    const _onCloseClick = useCallback((ev?: MouseEvent<HTMLButtonElement>) => {
        ev?.preventDefault();
        void fireEventAsync('FragmentInspector', onSelectFragment, null);
    }, [ onSelectFragment ]);

    const _onPreviousClick = useCallback((ev?: MouseEvent<HTMLAnchorElement>) => {
        ev?.preventDefault();
        if (previousFragmentId) {
            void fireEventAsync('FragmentInspector', onNextOrPreviousFragmentClick, previousFragmentId);
        }
    }, [ onNextOrPreviousFragmentClick, previousFragmentId ]);

    const _onNextClick = useCallback((ev?: MouseEvent<HTMLAnchorElement>) => {
        ev?.preventDefault();
        if (nextFragmentId) {
            void fireEventAsync('FragmentInspector', onNextOrPreviousFragmentClick, nextFragmentId);
        }
    }, [ onNextOrPreviousFragmentClick, nextFragmentId ]);

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

    useEffect(() => {
        if (! onNextOrPreviousFragmentClick) {
            return;
        }

        const __onKeyPress = (ev: KeyboardEvent) => {
            switch (ev.code) {
                case 'ArrowLeft':
                    _onPreviousClick();
                    break;
                case 'ArrowRight':
                    _onNextClick();
                    break;
                case 'Escape':
                    _onCloseClick();
                    break;
            }
        };
        document.addEventListener('keyup', __onKeyPress);
        return () => {
            document.removeEventListener('keyup', __onKeyPress);
        }
    }, [ onNextOrPreviousFragmentClick, _onPreviousClick, _onNextClick, _onCloseClick ]);

    /**
     * Jump to the component when it re-renders, as the customer is expecting
     * to see its content.
     */
    useEffect(() => {
        jumpToComponent(_rootRef.current);
    });

    return <aside className="fragment-inspector" ref={_rootRef}>
        <a href="#previous"
            className={classNames('navigation-arrows left', { 'disabled': ! previousFragmentId})}
            onClick={_onPreviousClick}>
            <TextIcon icon="chevron-left" />
        </a>
        <a href="#next"
            onClick={_onNextClick}
            className={classNames('navigation-arrows right', { 'disabled': ! nextFragmentId})}>
            <TextIcon icon="chevron-right" />
        </a>
        <button type="button" onClick={_onCloseClick} className="btn-close btn-close-white" />
        {fragment && <SentenceFragmentInspector fragment={fragment} />}
    </aside>;
}

export default FragmentInspector;

