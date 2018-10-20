import {
    Container
} from 'inversify';

import ApiConnector from './connectors/api';
import LanguageConnector from './connectors/languages';
import SessionCache from './utilities/session-cache';
import LocalCache from './utilities/local-cache';
import * as config from './config';

const container = new Container();

const api = new ApiConnector(config.ApiPath, config.ApiExceptionCollectorMethod, 
    config.ApiValidationFailedStatusCode);

container.bind(ApiConnector).toConstantValue(api);
container.bind(LanguageConnector).toSelf();
container.bind(config.InjectSessionCacheFactory).toConstructor(SessionCache);
container.bind(config.InjectLongTermCacheFactory).toConstructor(LocalCache);
