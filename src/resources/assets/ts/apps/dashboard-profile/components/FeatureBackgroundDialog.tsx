import { MouseEvent, useEffect, useState } from 'react';

import Dialog from "@root/components/Dialog";
import { IProps as IDialogProps } from '@root/components/Dialog._types';
import IAccountApi from "@root/connectors/backend/IAccountApi";
import { withPropInjection } from "@root/di";
import { DI } from '@root/di/keys';

interface IProps extends Pick<IDialogProps<any>, 'onDismiss'> {
    accountApi: IAccountApi;
    open?: boolean;
    onSelectBackground: ComponentEventHandler<string>;
}

import { fireEvent } from "@root/components/Component";
import { ComponentEventHandler } from "@root/components/Component._types";

import './FeatureBackgroundDialog.scss';

export function FeatureBackgroundDialog(props: IProps) {
    const {
        accountApi,
        open = false,
        onDismiss,
        onSelectBackground,
    } = props;

    const [ loading, setLoading ] = useState(true);
    const [ backgrounds, setBackgrounds ] = useState([]);

    useEffect(() => {
        if (accountApi) {
            void accountApi.getFeatureBackgrounds().then((r) => {
                setBackgrounds(
                    r.files.map((file) => `${r.path}/${file}`),
                );
            }).finally(() => {
                setLoading(false);
            });
        }
    }, []);

    const _onBackgroundClick = (background: string, ev: MouseEvent) => {
        ev.preventDefault();
        fireEvent('FeatureBackgroundDialog', onSelectBackground, background);
    }

    return <Dialog open={open}
        title="Select a background"
        size="lg"
        onDismiss={onDismiss}>
        <p>The background will be visible on your profile page as a backdrop to your avatar. Tap on the one you'd like to use! You can't upload your own background picture yet.</p>
        <div className="container">
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
