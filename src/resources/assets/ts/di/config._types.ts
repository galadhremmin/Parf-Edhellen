import { ITranscriber } from '@root/components/Tengwar._types';
import { IApiBaseConnector } from '@root/connectors/ApiConnector._types';
import IGlobalEvents from '@root/connectors/IGlobalEvents';
import IAccountApi from '@root/connectors/backend/IAccountApi';
import IBookApi from '@root/connectors/backend/IBookApi';
import IContributionResourceApi from '@root/connectors/backend/IContributionResourceApi';
import IDiscussApi from '@root/connectors/backend/IDiscussApi';
import ILexicalEntryResourceApi from '@root/connectors/backend/IGlossResourceApi';
import { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import { ILogApi } from '@root/connectors/backend/ILogApi';
import { ISentenceResourceApi } from '@root/connectors/backend/ISentenceResourceApi';
import ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import { ISubscriptionApi } from '@root/connectors/backend/ISubscriptionApi';
import IUtilityApi from '@root/connectors/backend/IUtilityApi';
import { IWordFinderApi } from '@root/connectors/backend/IWordFinderApi';
import { IRoleManager } from '@root/security';
import { IBrowserHistoryUtility } from '@root/utilities/BrowserHistory._types';

import { DI } from './keys';

export type DIContainerType = {
    [DI.AccountApi]?: IAccountApi,
    [DI.BackendApi]?: IApiBaseConnector;
    [DI.BookApi]?: IBookApi;
    [DI.BrowserHistory]?: IBrowserHistoryUtility;
    [DI.ContributionApi]?: IContributionResourceApi;
    [DI.DiscussApi]?: IDiscussApi;
    [DI.Glaemscribe]?: ITranscriber;
    [DI.GlossApi]?: ILexicalEntryResourceApi;
    [DI.InflectionApi]?: IInflectionResourceApi;
    [DI.LanguageApi]?: ILanguageApi;
    [DI.RoleManager]?: IRoleManager;
    [DI.SentenceApi]?: ISentenceResourceApi;
    [DI.SpeechApi]?: ISpeechResourceApi;
    [DI.SubscriptionApi]?: ISubscriptionApi;
    [DI.UtilityApi]?: IUtilityApi;
    [DI.LogApi]?: ILogApi;
    [DI.WordFinderApi]?: IWordFinderApi;
    [DI.GlobalEvents]?: IGlobalEvents;
}
