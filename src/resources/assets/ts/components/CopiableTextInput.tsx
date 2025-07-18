import React, { useCallback } from 'react';

import { excludeProps } from '@root/utilities/func/props';
import { IProps } from './CopiableTextInput._types';
import TextIcon from './TextIcon';
import { fireEventAsync } from './Component';

function CopiableTextInput(props: IProps) {
    const {
        formGroupClassName,
        onCopyActionFail,
        onCopyActionSuccess,
        value,
    } = props;
    const inputProps = excludeProps(props, [
        'formGroupClassName',
        'onCopyActionFail',
        'onCopyActionSuccess',
    ]);

    const _onCopy = useCallback((ev: React.MouseEvent<HTMLAnchorElement>) => {
        ev.preventDefault();

        if (value === null || value === undefined) {
            return;
        }

        const type = 'text/plain';
        const blob = new Blob([ value.toString() ], { type });
        const data = [new ClipboardItem({ [type]: blob })];

        navigator.clipboard.write(data).then(
            () => {
                void fireEventAsync('CopiableTextInput', onCopyActionSuccess, value);
            },
            (reason) => {
                void fireEventAsync('CopiableTextInput', onCopyActionFail, reason);
            },
        );
    }, [
        onCopyActionFail,
        onCopyActionSuccess,
        value,
    ]);

    return <div className={`input-group ${formGroupClassName}`}>
        <input className="form-control" {...inputProps} />
        <span className="input-group-text">
            <a href="#" title="Press to copy" onClick={_onCopy}><TextIcon icon="clipboard" /></a>
        </span>
    </div>;
}

export default CopiableTextInput;
