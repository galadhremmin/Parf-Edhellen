import {
    Container,
} from 'inversify';

import * as config from './config';

import ApiConnector from './connectors/ApiConnector';
import LanguageConnector from './connectors/LanguageConnector';
import LocalCache from './utilities/LocalCache';
import SessionCache from './utilities/SessionCache';

const container = new Container();
const api = new ApiConnector(config.ApiPath, config.ApiExceptionCollectorMethod,
    config.ApiValidationFailedStatusCode);

container.bind(ApiConnector).toConstantValue(api);
container.bind(LanguageConnector).toSelf();
container.bind(config.InjectSessionCacheFactory).toConstructor(SessionCache);
container.bind(config.InjectLongTermCacheFactory).toConstructor(LocalCache);
