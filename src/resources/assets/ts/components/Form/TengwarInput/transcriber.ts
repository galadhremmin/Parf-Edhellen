import LanguageConnector from '@root/connectors/backend/LanguageConnector';
import Glaemscribe from '@root/utilities/Glaemscribe';
import SharedReference from '@root/utilities/SharedReference';

export const transcribe = async (text: string, languageId: number) => {
    const languageConnector = SharedReference.getInstance(LanguageConnector);
    const language = await languageConnector.find(languageId);
    if (language === null) {
        return null;
    }

    const transcriber = SharedReference.getInstance(Glaemscribe);
    const transcribedText = await transcriber.transcribe(text, language.tengwarMode);
    return transcribedText || null;
};
