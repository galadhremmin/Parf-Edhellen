import { fireEventAsync } from '@root/components/Component';
import type { ComponentEventHandler } from '@root/components/Component._types';

const DragAndDropNotSupported = 'We unfortunately do not support drag and drop on your browser. Please tap on the image instead.';

export interface IResizeImageOptions {
    maxFileSize: number;
    maxWidthInPixels: number;
    fileName: string;
}

export function resizeAndUploadImage(imageFile: File, changeEvent: ComponentEventHandler<File>, options: IResizeImageOptions) {
    const { maxFileSize, maxWidthInPixels, fileName } = options;
    const tooLargeMessage = 'The picture you\'re trying to upload is too large. Please resize it and try again.';

    if (! imageFile) {
        alert(DragAndDropNotSupported);
        return;
    }

    new Promise<HTMLImageElement | null>((resolve, reject) => {
        if (imageFile.size < maxFileSize) {
            resolve(null);
        }

        try {
            const reader = new FileReader();
            reader.onload = ev => {
                const image = new Image();
                image.src = ev.target.result as string;
                resolve(image);
            };

            reader.readAsDataURL(imageFile);
        } catch (e) {
            reject(e as Error);
        }
    }).then(image => {
        if (! image) {
            return null;
        }

        const canvas = document.createElement('canvas');

        if (image.width >= image.height) {
            canvas.width  = maxWidthInPixels;
            canvas.height = canvas.width * (image.height / image.width);
        } else {
            canvas.width  = canvas.width * (image.width / image.height);
            canvas.height = maxWidthInPixels;
        }

        const context = canvas.getContext('2d');
        if (! context) {
            return Promise.reject(new Error(tooLargeMessage));
        }

        context.drawImage(image, 0, 0, canvas.width, canvas.height);

        return new Promise<File>(resolve => {
            canvas.toBlob(blob => {
                const newImageFile = new File([blob], fileName, { type: 'image/png' });
                resolve(newImageFile);
            }, 'image/png', 0.85);
        });
    }).then(resizedImageFile => {
        if (resizedImageFile?.size > maxFileSize) {
            return Promise.reject(new Error(tooLargeMessage));
        }

        return resizedImageFile;
    }).then(resizedImageFile => {
        void fireEventAsync('resizeAndUploadImage', changeEvent, resizedImageFile ?? imageFile);
    }).catch((error) => {
        alert('Your image unfortunately can\'t be uploaded due to an expected error. Error: ' + error);
    });
}
