/// <reference path="../_types/glaemscribe.d.ts" />
import { DefaultGlaemscribeCharacterSet } from '../config';

/**
 * Transcribes the specified text body to tengwar using the parmaite font.
 * @param {string} text
 * @param {string} mode
 */
export default class Transcriber {
    private static _modes: any = {};
    private static _charset = false;

    public async transcribe(text: string, mode: string) {
        await this.initializeGlaemscribe(mode);

        const transcriber = Glaemscribe.resource_manager.loaded_modes[mode];
        const charset = Glaemscribe.resource_manager.loaded_charsets[DefaultGlaemscribeCharacterSet];
        if (!transcriber) {
            return undefined;
        }

        // Transcribe using Glaemscribe transcriber for the specified mode
        // The result is an array with three elements:
        // 0th element: whether the transcription was successful (true/false)
        // 1th element: transcription result
        // 2th element: debug data
        const result = transcriber.transcribe(text, charset);
        if (!result[0]) {
            return undefined; // failed!
        }

        // Return the transcription results
        return result[1].trim();
    }

    public async initializeGlaemscribe(mode: string) {
        let transcriber = this._getGlaemscribe();
        if (! transcriber) {
            transcriber = await this._loadGlaemscribe();
        }

        if (Transcriber._modes.hasOwnProperty(mode) === false) {
            await this._loadMode(mode);
            transcriber.resource_manager.load_modes(mode);
            Transcriber._modes[mode] = true;
        }

        if (Transcriber._charset === false) {
            await this._loadCharset();
            transcriber.resource_manager.load_charsets(DefaultGlaemscribeCharacterSet);
            Transcriber._charset = true;
        }
    }

    private _getGlaemscribe() {
        return (window as any).Glaemscribe;
    }

    private _setGlaemscribe(transcriber: IGlaemscribe) {
        (window as any).Glaemscribe = transcriber;
    }

    private async _loadGlaemscribe(): Promise<IGlaemscribe> {
        // Load and execute Glaemscribe
        const Glaemscribe = await import('glaemscribe/js/glaemscribe.min.js');
        this._setGlaemscribe(Glaemscribe);
        return Glaemscribe;
    }

    private async _loadCharset() {
        try {
            return await import(`glaemscribe/js/charsets/${DefaultGlaemscribeCharacterSet}.cst.js`);
        } catch (ex) {
            throw new Error(`${DefaultGlaemscribeCharacterSet} is not included in the current application bundle: ${ex}.`);
        }
    }

    private _loadMode(mode: string) {
        return import(`glaemscribe/js/modes/${mode}.glaem.js`);
    }
}
