import classNames from 'classnames';
import React, {
    useCallback,
    useRef,
    useState,
} from 'react';

import Avatar from '@root/components/Avatar';
import { fireEventAsync } from '@root/components/Component';
import { IProps } from './AvatarForm._types';

import './AvatarForm.scss';

function AvatarForm(props: IProps) {
    const {
        onAvatarChange,
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
            for (let i = 0; i < ev.dataTransfer.items.length; i += 1) {
                const file = ev.dataTransfer.items.item(i);
                if (file.kind === 'file' && file.type.indexOf('image') !== -1) {
                    imageFile = file.getAsFile();
                    break;
                }
            }
        } else {
            for (let i = 0; i < ev.dataTransfer.files.length; i += 1) {
                const file = ev.dataTransfer.files.item(i);
                if (file.type.indexOf('image') !== -1) {
                    imageFile = file;
                    break;
                }
            }
        }

        if (imageFile !== null) {
            fireEventAsync('Avatar', onAvatarChange, imageFile);
            imageFile = null;
        }

        setShowCallToAction(false);
    }, [ onAvatarChange ]);

    const _onFileChange = useCallback((ev: React.ChangeEvent<HTMLInputElement>) => {
        ev.preventDefault();
        if (ev.target.files.length > 0) {
            fireEventAsync('Avatar', onAvatarChange, ev.target.files[0]);
        }
    }, [ onAvatarChange ]);

    const _onClick = useCallback(() => {
        if (fileComponent.current) {
            fileComponent.current.click();
        }
    }, [ fileComponent ]);

    return <Avatar
        onClick={_onClick}
        onDrop={_onDrop}
        onDragOver={_onShowCallToAction}
        onDragEnd={_onHideCallToAction}
        onDragExit={_onHideCallToAction}
        onDragLeave={_onHideCallToAction}
        path={path}>
        <span className={classNames('Avatar--picture__drophere', {
            show: showCallToAction,
        })}>Drop your photo here</span>
        <input type="file" ref={fileComponent} onChange={_onFileChange} />
    </Avatar>;
}

export default AvatarForm;
