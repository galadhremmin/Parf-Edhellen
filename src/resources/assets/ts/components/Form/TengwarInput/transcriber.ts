import ILanguageApi from '@root/connectors/backend/ILanguageApi';
import { DI, resolve } from '@root/di';
import Glaemscribe from '@root/utilities/Glaemscribe';

export const transcribe = async (text: string, languageId: number) => {
    const languageConnector = resolve<ILanguageApi>(DI.LanguageApi);
    const language = await languageConnector.find(languageId);
    if (language === null) {
        return null;
    }

    const transcriber = resolve<Glaemscribe>(DI.Glaemscribe);
    const transcribedText = await transcriber.transcribe(text, language.tengwarMode);
    return transcribedText || null;
};
