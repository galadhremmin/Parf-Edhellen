import classNames from 'classnames';
import {
    useCallback,
    useRef,
    useState,
} from 'react';
import type { DragEvent, ChangeEvent } from 'react';

import Avatar from '@root/components/Avatar';
import { fireEventAsync } from '@root/components/Component';
import type { ComponentEventHandler } from '@root/components/Component._types';
import { AvatarMaximiumFileSize, AvatarMaximumImageWidthInPixels } from '@root/config';
import type { IProps } from './AvatarForm._types';

import './AvatarForm.scss';

const TooLargeAvatarInTermsOfFileSize = 'The picture you\'re trying to upload is too large. Please resize it and try again.';
const DragAndDropNotSupported = 'We unfortunately do not support drag and drop on your browser. Please tap on the avatar instead.';

function uploadImage(imageFile: File, changeEvent: ComponentEventHandler<File>) {
    if (! imageFile) {
        alert(DragAndDropNotSupported);
        return;
    }

    new Promise<HTMLImageElement | null>((resolve, reject) => {
        if (imageFile.size < AvatarMaximiumFileSize) {
            resolve(null);
        }

        try {
            const reader = new FileReader();
            //This is for previewing the image
            reader.onload = ev => {
                const image = new Image();
                image.src = ev.target.result as string;
                resolve(image);
            };

            reader.readAsDataURL(imageFile);
        } catch (e) {
            // whops todo
            reject(e as Error);
        }
    }).then(image => {
        if (! image) {
            return null;
        }

        const canvas = document.createElement('canvas');

        if (image.width <= image.height) {
            canvas.width  = AvatarMaximumImageWidthInPixels;
            canvas.height = canvas.width * (image.height / image.width);
        } else {
            canvas.width  = canvas.width * (image.width / image.height);
            canvas.height = AvatarMaximumImageWidthInPixels;
        }

        const context = canvas.getContext('2d');
        if (! context) {
            return Promise.reject(new Error(TooLargeAvatarInTermsOfFileSize));
        }

        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        return new Promise<File>(resolve => {
            canvas.toBlob(blob => {
                const newImageFile = new File([blob], 'avatar.png', { type: 'image/png' });
                resolve(newImageFile);
            }, 'image/png', 0.85);
        });
    }).then(resizedImageFile => {
        if (resizedImageFile?.size > AvatarMaximiumFileSize) {
            return Promise.reject(new Error(TooLargeAvatarInTermsOfFileSize));
        }

        return resizedImageFile;
    }).then(resizedImageFile => {
        void fireEventAsync('Avatar', changeEvent, resizedImageFile ?? imageFile);
    }).catch((error) => {
        // todo - send error somewhere
        alert('Your avatar unfortunately can\'t be changed due to an expected error. Error: ' + error);
    });
}

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
        uploadImage(ev.dataTransfer.files.item(0), onAvatarChange);
    }, [ onAvatarChange ]);

    const _onFileChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        ev.preventDefault();
        if (ev.target.files.length > 0) {
            uploadImage(ev.target.files[0], onAvatarChange);
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
