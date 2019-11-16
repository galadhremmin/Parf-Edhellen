import classNames from 'classnames';
import React, {
    useCallback,
    useRef,
    useState,
} from 'react';

import { fireEventAsync } from '@root/components/Component';
import { IProps } from './Avatar._types';

import './Avatar.scss';

function Avatar(props: IProps) {
    const {
        onChange,
        path,
    } = props;

    const fileComponent = useRef<HTMLInputElement>(null);
    const [ showCallToAction, setShowCallToAction ] = useState(false);

    const _onShowCallToAction = useCallback((ev: any) => {
        ev.preventDefault();
        setShowCallToAction(true);
    }, []);

    const _onHideCallToAction = useCallback((ev: any) => {
        setShowCallToAction(false);
    }, []);

    const _onDrop = useCallback((ev: React.DragEvent<HTMLDivElement>) => {
        // Prevent default behavior (Prevent file from being opened)
        ev.preventDefault();

        let imageFile: File = null;
        if (ev.dataTransfer.items) {
            for (const file of ev.dataTransfer.items) {
                if (file.kind === 'file' && file.type.indexOf('image') !== -1) {
                    imageFile = file.getAsFile();
                    break;
                }
            }
        } else {
            for (const file of ev.dataTransfer.files) {
                if (file.type.indexOf('image') !== -1) {
                    imageFile = file;
                    break;
                }
            }
        }

        if (imageFile !== null) {
            fireEventAsync('Avatar', onChange, imageFile);
            imageFile = null;
        }

        setShowCallToAction(false);
    }, [ onChange ]);

    const _onFileChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        ev.preventDefault();
        if (ev.target.files.length > 0) {
            fireEventAsync('Avatar', onChange, ev.target.files[0]);
        }
    }, [ onChange ]);

    const _onClick = useCallback(() => {
        if (fileComponent.current) {
            fileComponent.current.click();
        }
    }, [ fileComponent ]);

    return <div className="Avatar--picture"
        onClick={_onClick}
        onDrop={_onDrop}
        onDragOver={_onShowCallToAction}
        onDragEnd={_onHideCallToAction}
        onDragExit={_onHideCallToAction}
        onDragLeave={_onHideCallToAction}
        style={{ backgroundImage: `url(${path})` }}>
        <span className={classNames('Avatar--picture__drophere', {
            show: showCallToAction,
        })}>Drop your photo here</span>
        <input type="file" ref={fileComponent} onChange={_onFileChange} />
    </div>;
}

export default Avatar;
