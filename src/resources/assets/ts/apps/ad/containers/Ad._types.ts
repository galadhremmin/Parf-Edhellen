import type { IGlobalAdConfiguration } from '../index._types';

export interface IProps extends IGlobalAdConfiguration {
    onMount?: () => void;
}
