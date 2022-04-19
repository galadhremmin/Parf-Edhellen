import { GlobalAdsConfigurationName } from "@root/config";

export type AdName = string;

export interface IProps {
    ad: AdName;
}

export interface IGlobalAdConfiguration {
    dataset?: {
        [key: string]: any;
    };
    props?: any;
}

export type IGlobalAdsConfiguration = {
    [adName: AdName]: IGlobalAdConfiguration;
} & {
    _mount?: () => void;
};

export type WindowWithAds = Window & {
    [GlobalAdsConfigurationName]?: IGlobalAdsConfiguration;
};
