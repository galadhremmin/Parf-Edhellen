export interface IProps {
    ad: string;
}

export interface IGlobalAdConfiguration {
    dataset?: {
        [key: string]: any;
    };
    props?: any;
}

export type IGlobalAdsConfiguration = {
    [adName: string]: IGlobalAdConfiguration;
} & {
    _mount?: () => void;
};
