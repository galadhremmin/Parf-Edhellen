import ApiConnector from '@root/connectors/ApiConnector';
import GlobalEventConnector from '@root/connectors/GlobalEventConnector';
import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import ContributionResourceApiConnector from '@root/connectors/backend/ContributionResourceApiConnector';
import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import GlossResourceApiConnector from '@root/connectors/backend/GlossResourceApiConnector';
import InflectionResourceApiConnector from '@root/connectors/backend/InflectionResourceApiConnector';
import LanguageConnector from '@root/connectors/backend/LanguageConnector';
import PasskeyApiConnector from '@root/connectors/backend/PasskeyApiConnector';
import SentenceResourceApiConnector from '@root/connectors/backend/SentenceApiConnector';
import SpeechResourceApiConnector from '@root/connectors/backend/SpeechResourceApiConnector';
import SubscriptionApiConnector from '@root/connectors/backend/SubscriptionApiConnector';
import UtilityApiConnector from '@root/connectors/backend/UtilityApiConnector';
import WordFinderConnector from '@root/connectors/backend/WordFinderConnector';
import { RoleManager } from '@root/security';
import BrowserHistory from '@root/utilities/BrowserHistory';
import { default as GlaemscribeUtility } from '@root/utilities/Glaemscribe';
import {
    setInstance,
    setSingleton,
} from '.';
import { DI } from './keys';

export default function setupContainer() {
    setSingleton(DI.AccountApi, AccountApiConnector);
    setSingleton(DI.BackendApi, ApiConnector);
    setSingleton(DI.BookApi, BookApiConnector);
    setSingleton(DI.BrowserHistory, BrowserHistory);
    setSingleton(DI.ContributionApi, ContributionResourceApiConnector);
    setSingleton(DI.DiscussApi, DiscussApiConnector);
    setSingleton(DI.Glaemscribe, GlaemscribeUtility);
    setSingleton(DI.GlossApi, GlossResourceApiConnector);
    setSingleton(DI.InflectionApi, InflectionResourceApiConnector);
    setSingleton(DI.LanguageApi, LanguageConnector);
    setSingleton(DI.PasskeyApi, PasskeyApiConnector);
    setSingleton(DI.RoleManager, RoleManager);
    setSingleton(DI.SentenceApi, SentenceResourceApiConnector);
    setSingleton(DI.SpeechApi, SpeechResourceApiConnector);
    setSingleton(DI.SubscriptionApi, SubscriptionApiConnector);
    setSingleton(DI.UtilityApi, UtilityApiConnector);
    setSingleton(DI.LogApi, UtilityApiConnector);
    setSingleton(DI.WordFinderApi, WordFinderConnector);
    setInstance(DI.GlobalEvents, GlobalEventConnector);
}
