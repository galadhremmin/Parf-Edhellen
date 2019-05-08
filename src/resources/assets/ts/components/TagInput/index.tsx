import React, {
    useCallback,
    useState,
} from 'react';

import { fireEvent } from '../Component';
import { IProps } from './index._types';

function TagInput(props: IProps) {
    const {
        name,
        onChange,
        value: tags,
    } = props;

    // Tags must be an array. All other values are unacceptable.
    if (! Array.isArray(tags)) {
        const json = JSON.stringify(tags);
        throw new Error(`Expected value property to be an array of strings, but received ${json}.`);
    }

    const [ textValue, setTextValue ] = useState(() => '');

    const _addTag = (tag: string) => {
        setTextValue('');

        // Protect against incorrect `null` and empty string (or whitespace).
        if (! tag || /^\s*$/.test(tag)) {
            return;
        }

        // Do not add the tag if it already exists 
        if (tags.indexOf(tag) > -1) {
            return;
        }

        tags.push(tag);
        tags.sort(); // TODO: locale compare to sort alphabetically

        fireEvent(name, onChange, tags);
    };

    const _onTextChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        setTextValue(ev.target.value);
    }, []);

    const _onBlur = useCallback((ev: React.FocusEvent<HTMLInputElement>) => {
        _addTag(textValue);
    }, [ textValue, tags ]);

    const _onKeyPress = useCallback((ev: React.KeyboardEvent<HTMLInputElement>) => {
        if (ev.which === 13) {
            ev.preventDefault();
            _addTag(textValue);
        }
    }, [ textValue, tags ]);

    return <>
        {tags.map((tag: string) => <span key={tag} />)}
        <input className="form-control"
            onBlur={_onBlur}
            onChange={_onTextChange}
            onKeyPress={_onKeyPress}
            type="text"
            value={textValue} 
        />
    </>;
}

TagInput.defaultProps = {
    value: [],
} as Partial<IProps>;

export default TagInput;
