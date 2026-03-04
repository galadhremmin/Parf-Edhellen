import classNames from '@root/utilities/ClassNames';
import {
    useCallback,
    useRef,
    useState,
} from 'react';
import type { DragEvent, ChangeEvent } from 'react';

import Avatar from '@root/components/Avatar';
import { AvatarMaximiumFileSize, AvatarMaximumImageWidthInPixels } from '@root/config';
import { resizeAndUploadImage } from '@root/utilities/images';
import type { IProps } from './AvatarForm._types';

import './AvatarForm.scss';

const avatarUploadOptions = {
    maxFileSize: AvatarMaximiumFileSize,
    maxWidthInPixels: AvatarMaximumImageWidthInPixels,
    fileName: 'avatar.png',
};

function AvatarForm(props: IProps) {
    const {
        onAvatarChange,
        path,
    } = props;

    const fileComponent = useRef<HTMLInputElement>(null);
    const [ showCallToAction, setShowCallToAction ] = useState(false);

    const _onShowCallToAction = useCallback((ev: DragEvent<HTMLDivElement>) => {
        ev.preventDefault();
        setShowCallToAction(true);
    }, []);

    const _onHideCallToAction = useCallback((ev: DragEvent<HTMLDivElement>) => {
        ev.preventDefault();
        setShowCallToAction(false);
    }, []);

    const _onDrop = useCallback((ev: DragEvent<HTMLDivElement>) => {
        // Prevent default behavior (Prevent file from being opened)
        ev.preventDefault();
        resizeAndUploadImage(ev.dataTransfer.files.item(0), onAvatarChange, avatarUploadOptions);
    }, [ onAvatarChange ]);

    const _onFileChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        ev.preventDefault();
        if (ev.target.files.length > 0) {
            resizeAndUploadImage(ev.target.files[0], onAvatarChange, avatarUploadOptions);
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
