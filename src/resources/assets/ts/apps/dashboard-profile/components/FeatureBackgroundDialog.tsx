import { type ChangeEvent, type DragEvent, type MouseEvent, useCallback, useEffect, useRef, useState } from 'react';

import Dialog from "@root/components/Dialog";
import type { IProps as IDialogProps } from '@root/components/Dialog._types';
import type IAccountApi from "@root/connectors/backend/IAccountApi";
import { withPropInjection } from "@root/di";
import { DI } from '@root/di/keys';

interface IProps extends Pick<IDialogProps<any>, 'onDismiss'> {
    accountApi: IAccountApi;
    open?: boolean;
    onSelectBackground: ComponentEventHandler<string>;
    onUploadBackground: ComponentEventHandler<File>;
}

import { fireEvent } from "@root/components/Component";
import type { ComponentEventHandler } from "@root/components/Component._types";
import { BackgroundMaximumFileSize, BackgroundMaximumImageWidthInPixels } from "@root/config";
import classNames from "@root/utilities/ClassNames";
import { resizeAndUploadImage } from "@root/utilities/images";

import './FeatureBackgroundDialog.scss';

const backgroundUploadOptions = {
    maxFileSize: BackgroundMaximumFileSize,
    maxWidthInPixels: BackgroundMaximumImageWidthInPixels,
    fileName: 'background.png',
};

export function FeatureBackgroundDialog(props: IProps) {
    const {
        accountApi,
        open = false,
        onDismiss,
        onSelectBackground,
        onUploadBackground,
    } = props;

    const [ loading, setLoading ] = useState(true);
    const [ backgrounds, setBackgrounds ] = useState([]);
    const [ showDropHint, setShowDropHint ] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (accountApi) {
            accountApi.getFeatureBackgrounds().then((r) => {
                setBackgrounds(
                    r.files.map((file) => `${r.path}/${file}`),
                );
            }) //
            .catch(() => {
                setBackgrounds([]);
            }) //
            .finally(() => {
                setLoading(false);
            });
        }
    }, []);

    const _onBackgroundClick = (background: string, ev: MouseEvent) => {
        ev.preventDefault();
        void fireEvent('FeatureBackgroundDialog', onSelectBackground, background);
    };

    const _onDropZoneClick = useCallback(() => {
        fileInputRef.current?.click();
    }, []);

    const _onDragOver = useCallback((ev: DragEvent<HTMLDivElement>) => {
        ev.preventDefault();
        setShowDropHint(true);
    }, []);

    const _onDragLeave = useCallback((ev: DragEvent<HTMLDivElement>) => {
        ev.preventDefault();
        setShowDropHint(false);
    }, []);

    const _onDrop = useCallback((ev: DragEvent<HTMLDivElement>) => {
        ev.preventDefault();
        setShowDropHint(false);
        resizeAndUploadImage(ev.dataTransfer.files.item(0), onUploadBackground, backgroundUploadOptions);
    }, [ onUploadBackground ]);

    const _onFileChange = useCallback((ev: ChangeEvent<HTMLInputElement>) => {
        ev.preventDefault();
        if (ev.target.files.length > 0) {
            resizeAndUploadImage(ev.target.files[0], onUploadBackground, backgroundUploadOptions);
        }
    }, [ onUploadBackground ]);

    return <Dialog open={open}
        title="Select a background"
        size="lg"
        onDismiss={onDismiss}>
        <p>The background will be visible on your profile page as a backdrop to your avatar. Upload your own or tap one of the options below!</p>

        <div className={classNames('FeatureBackgroundDialog--dropzone', { 'FeatureBackgroundDialog--dropzone__active': showDropHint })}
            onClick={_onDropZoneClick}
            onDragOver={_onDragOver}
            onDragLeave={_onDragLeave}
            onDragEnd={_onDragLeave}
            onDrop={_onDrop}>
            <span>{showDropHint ? 'Release to upload' : 'Drop an image here or click to browse'}</span>
            <input type="file" accept="image/*" ref={fileInputRef} onChange={_onFileChange} />
        </div>

        <hr className="next-overlaps" />
        <span>or pick one below</span>

        <div className="container mt-4">
            <div className="row gy-3 gx-3">
                {backgrounds.map((background) => <div className="col-md-4 col-sm-6" key={background}>
                    <a href={background} onClick={(ev) => _onBackgroundClick(background, ev)}>
                        <img src={background} className="w-100 rounded shadow feature-background" />
                    </a>
                </div>)}
            </div>
        </div>
    </Dialog>
}

export default withPropInjection(FeatureBackgroundDialog, {
    accountApi: DI.AccountApi,
});
