import ApiConnector from '@root/connectors/ApiConnector';
import AccountApiConnector from '@root/connectors/backend/AccountApiConnector';
import BookApiConnector from '@root/connectors/backend/BookApiConnector';
import ContributionResourceApiConnector from '@root/connectors/backend/ContributionResourceApiConnector';
import DiscussApiConnector from '@root/connectors/backend/DiscussApiConnector';
import GlossResourceApiConnector from '@root/connectors/backend/GlossResourceApiConnector';
import InflectionResourceApiConnector from '@root/connectors/backend/InflectionResourceApiConnector';
import LanguageConnector from '@root/connectors/backend/LanguageConnector';
import SpeechResourceApiConnector from '@root/connectors/backend/SpeechResourceApiConnector';
import SubscriptionApiConnector from '@root/connectors/backend/SubscriptionApiConnector';
import UtilityApiConnector from '@root/connectors/backend/UtilityApiConnector';
import WordFinderConnector from '@root/connectors/backend/WordFinderConnector';
import { RoleManager } from '@root/security';
import BrowserHistory from '@root/utilities/BrowserHistory';
import { default as GlaemscribeUtility } from '@root/utilities/Glaemscribe';
import {
    singleton,
} from '.';
import { DI } from './keys';

export default function setupContainer() {
    singleton(DI.AccountApi, AccountApiConnector);
    singleton(DI.BackendApi, ApiConnector);
    singleton(DI.BookApi, BookApiConnector);
    singleton(DI.BrowserHistory, BrowserHistory);
    singleton(DI.ContributionApi, ContributionResourceApiConnector);
    singleton(DI.DiscussApi, DiscussApiConnector);
    singleton(DI.Glaemscribe, GlaemscribeUtility);
    singleton(DI.GlossApi, GlossResourceApiConnector);
    singleton(DI.InflectionApi, InflectionResourceApiConnector);
    singleton(DI.LanguageApi, LanguageConnector);
    singleton(DI.RoleManager, RoleManager);
    singleton(DI.SpeechApi, SpeechResourceApiConnector);
    singleton(DI.SubscriptionApi, SubscriptionApiConnector);
    singleton(DI.UtilityApi, UtilityApiConnector);
    singleton(DI.LogApi, ApiConnector);
    singleton(DI.WordFinderApi, WordFinderConnector);
}
