import type { ITranscriber } from '@root/components/Tengwar._types';
import type { IApiBaseConnector } from '@root/connectors/ApiConnector._types';
import type IGlobalEvents from '@root/connectors/IGlobalEvents';
import type IAccountApi from '@root/connectors/backend/IAccountApi';
import type IBookApi from '@root/connectors/backend/IBookApi';
import type IContributionResourceApi from '@root/connectors/backend/IContributionResourceApi';
import type IDiscussApi from '@root/connectors/backend/IDiscussApi';
import type ILexicalEntryResourceApi from '@root/connectors/backend/IGlossResourceApi';
import type { IInflectionResourceApi } from '@root/connectors/backend/IInflectionResourceApi';
import type ILanguageApi from '@root/connectors/backend/ILanguageApi';
import type { ILogApi } from '@root/connectors/backend/ILogApi';
import type IPasskeyApi from '@root/connectors/backend/IPasskeyApi';
import type { ISentenceResourceApi } from '@root/connectors/backend/ISentenceResourceApi';
import type ISpeechResourceApi from '@root/connectors/backend/ISpeechResourceApi';
import type { ISubscriptionApi } from '@root/connectors/backend/ISubscriptionApi';
import type IUtilityApi from '@root/connectors/backend/IUtilityApi';
import type { IWordFinderApi } from '@root/connectors/backend/IWordFinderApi';
import type { IRoleManager } from '@root/security';
import type { IBrowserHistoryUtility } from '@root/utilities/BrowserHistory._types';

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
    [DI.PasskeyApi]?: IPasskeyApi;
    [DI.RoleManager]?: IRoleManager;
    [DI.SentenceApi]?: ISentenceResourceApi;
    [DI.SpeechApi]?: ISpeechResourceApi;
    [DI.SubscriptionApi]?: ISubscriptionApi;
    [DI.UtilityApi]?: IUtilityApi;
    [DI.LogApi]?: ILogApi;
    [DI.WordFinderApi]?: IWordFinderApi;
    [DI.GlobalEvents]?: IGlobalEvents;
}
