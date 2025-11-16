import classNames from '@root/utilities/ClassNames';
import {
    useCallback,
    useState,
    type ChangeEvent,
} from 'react';

import TextIcon from '@root/components/TextIcon';
import { fireEvent } from '../../Component';
import type { IProps } from './TagLabel._types';

function TagLabel(props: IProps) {
    const {
        tag,
        onDelete,
    } = props;

    const [ focused, setFocused ] = useState(false);

    const _onTagClick = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        if (! ev.target.checked) {
            void fireEvent(`Tag[${tag}]`, onDelete, tag);
        }
    }, [ tag, onDelete ]);

    const _onTagBlur = useCallback(() => {
        setFocused(false);
    }, [ setFocused ]);

    const _onTagFocus = useCallback(() => {
        setFocused(true);
    }, [ setFocused ]);

    return <label className={classNames('badge', 'fs-6', 'border', 'bg-light', 'text-dark', { focused })}>
        <input checked={true}
               name={`tag-checkbox--${tag}`}
               onBlur={_onTagBlur}
               onChange={_onTagClick}
               onFocus={_onTagFocus}
               type="checkbox"
        />
        {tag}
        &#32;
        <TextIcon icon="trash" />
    </label>;
}

export default TagLabel;
