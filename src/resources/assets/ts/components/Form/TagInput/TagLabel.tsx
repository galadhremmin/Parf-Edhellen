import classNames from 'classnames';
import React, {
    useCallback,
    useState,
} from 'react';

import TextIcon from '@root/components/TextIcon';
import { fireEvent } from '../../Component';
import { IProps } from './TagLabel._types';

function TagLabel(props: IProps) {
    const {
        tag,
        onDelete,
    } = props;

    const [ focused, setFocused ] = useState(false);

    const _onTagClick = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        if (! ev.target.checked) {
            fireEvent(`Tag[${tag}]`, onDelete, tag);
        }
    }, [ tag, onDelete ]);

    const _onTagBlur = useCallback(() => {
        setFocused(false);
    }, [ setFocused ]);

    const _onTagFocus = useCallback(() => {
        setFocused(true);
    }, [ setFocused ]);

    return <label className={classNames({ focused })}>
        <input checked={true}
               name={`tag-checkbox--${tag}`}
               onBlur={_onTagBlur}
               onChange={_onTagClick}
               onFocus={_onTagFocus}
               type="checkbox"
        />
        {tag}
        &#32;
        <TextIcon icon="remove-sign" />
    </label>;
}

export default TagLabel;
