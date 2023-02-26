import { INewable } from './_types';
import ApiConnector from './connectors/ApiConnector';
import AccountApiConnector from './connectors/backend/AccountApiConnector';
import BookApiConnector from './connectors/backend/BookApiConnector';
import ContributionResourceApiConnector from './connectors/backend/ContributionResourceApiConnector';
import DiscussApiConnector from './connectors/backend/DiscussApiConnector';
import GlossResourceApiConnector from './connectors/backend/GlossResourceApiConnector';
import InflectionResourceApiConnector from './connectors/backend/InflectionResourceApiConnector';
import LanguageConnector from './connectors/backend/LanguageConnector';
import SpeechResourceApiConnector from './connectors/backend/SpeechResourceApiConnector';
import SubscriptionApiConnector from './connectors/backend/SubscriptionApiConnector';
import UtilityApiConnector from './connectors/backend/UtilityApiConnector';
import WordFinderConnector from './connectors/backend/WordFinderConnector';
import { RoleManager } from './security';
import BrowserHistory from './utilities/BrowserHistory';
import Glaemscribe from './utilities/Glaemscribe';
import SharedReference from './utilities/SharedReference';

// This is a temporary mitigation in lieu of a better solution
export enum DI {
    AccountApi = 'AccountApi',
    BackendApi = 'BackendApi',
    BookApi = 'BookApi',
    BrowserHistory = 'BrowserHistory',
    ContributionApi = 'ContributionApi',
    DiscussApi = 'DiscussApi',
    Glaemscribe = 'Glaemscribe',
    GlossApi = 'GlossApi',
    InflectionApi = 'InflectionApi',
    LanguageApi = 'LanguageApi',
    RoleManager = 'RoleManager',
    SpeechApi = 'SpeechApi',
    SubscriptionApi = 'SubscriptionApi',
    UtilityApi = 'UtilityApi',
    WordFinderApi = 'WordFinderApi',
}

export const getType = (name: DI): any => {
    switch (name) {
        case DI.AccountApi:
            return AccountApiConnector;
        case DI.BackendApi:
            return ApiConnector;
        case DI.BookApi:
            return BookApiConnector;
        case DI.BrowserHistory:
            return BrowserHistory;
        case DI.ContributionApi:
            return ContributionResourceApiConnector;
        case DI.DiscussApi:
            return DiscussApiConnector;
        case DI.Glaemscribe:
            return Glaemscribe;
        case DI.GlossApi:
            return GlossResourceApiConnector;
        case DI.InflectionApi:
            return InflectionResourceApiConnector;
        case DI.LanguageApi:
            return LanguageConnector;
        case DI.RoleManager:
            return RoleManager;
        case DI.SpeechApi:
            return SpeechResourceApiConnector;
        case DI.SubscriptionApi:
            return SubscriptionApiConnector;
        case DI.UtilityApi:
            return UtilityApiConnector;
        case DI.WordFinderApi:
            return WordFinderConnector;
        default:
            throw new Error(`Unrecognised DI ${name}.`);
    }
};

export const resolve = <T>(name: DI) => {
    const type = getType(name);
    return new SharedReference(type, name).value as T;
};
