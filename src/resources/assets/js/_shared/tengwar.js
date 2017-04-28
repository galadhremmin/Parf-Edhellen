import he from 'he';
import { transcribe as transcribeGeneralUse, makeOptions as makeOptionsForGeneralUse } from 'tengwar/general-use';
import { transcribe as transcribeClassical, makeOptions as makeOptionsForClassical } from 'tengwar/classical';
import TengwarParmaite from 'tengwar/tengwar-parmaite';

/**
 * Transcribes the specified text body to tengwar using the parmaite font.
 * @param {string} text 
 * @param {string} mode 
 * @param {Boolean} [html=true]
 */
export const transcribe = (text, mode, html) => {
    if (html === undefined) {
      html = true;
    }
    
    let options = null;
    let transcriber = null;

    switch (mode) {
        case 'general-use':
            options = makeOptionsForGeneralUse();
            transcriber = transcribeGeneralUse;
            break;
        case 'classical':
            options = makeOptionsForClassical();
            transcriber = transcribeClassical;
            break;
        default:
            // unsupported!
            return undefined;
    }

    options.font = TengwarParmaite;
    options.plain = !html;
    options.block = false;

    let transcription = transcriber(text, options);
    if (!transcription) {
      return undefined;
    }

    // Decode HTML entities. 
    // See https://github.com/kriskowal/tengwarjs/issues/10
    if (!html) {
      transcription = he.decode(transcription);
    }

    return transcription;
};
