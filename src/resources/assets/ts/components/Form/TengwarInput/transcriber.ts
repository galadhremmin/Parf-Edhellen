import { resolve } from '@root/di';
import { DI } from '@root/di/keys';

export const transcribe = async (text: string, languageId: number) => {
    const languageConnector = resolve(DI.LanguageApi);
    const language = await languageConnector.find(languageId, 'id');
    if (language === null) {
        return null;
    }

    const transcriber = resolve(DI.Glaemscribe);
    const transcribedText = await transcriber.transcribe(text, language.tengwarMode);
    return transcribedText || null;
};
