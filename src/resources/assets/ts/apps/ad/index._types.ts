export interface IProps {
    ad: string;
}

export interface IGlobalAdConfiguration {
    dataset: {
        [key: string]: any;
    };
    props: any;
}

export interface IGlobalAdsConfiguration {
    [adName: string]: IGlobalAdConfiguration;
}
