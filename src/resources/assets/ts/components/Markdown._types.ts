import IUtilityApi from '@root/connectors/backend/IUtilityApi';

export interface IProps {
    parse: boolean;
    text: string;

    markdownApi?: IUtilityApi;
}
