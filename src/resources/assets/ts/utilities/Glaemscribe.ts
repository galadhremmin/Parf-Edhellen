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
        if ('Glaemscribe' in window === false) {
            await this._loadGlaemscribe();
        }

        if (Transcriber._modes.hasOwnProperty(mode) === false) {
            await this._loadMode(mode);
            Glaemscribe.resource_manager.load_modes(mode);
            Transcriber._modes[mode] = true;
        }

        if (Transcriber._charset === false) {
            await this._loadCharset();
            Glaemscribe.resource_manager.load_charsets(DefaultGlaemscribeCharacterSet);
            Transcriber._charset = true;
        }
    }

    private async _loadGlaemscribe() {
        // Load and execute Glaemscribe
        return await import('glaemscribe/js/glaemscribe.min.js');
    }

    private async _loadCharset() {
        if (DefaultGlaemscribeCharacterSet === 'tengwar_ds_annatar') {
            return await import(`glaemscribe/js/charsets/tengwar_ds_annatar.cst.js`);
        }

        throw new Error(`${DefaultGlaemscribeCharacterSet} is not included in the current application bundle.`);
    }

    private _loadMode(mode: string) {
        switch (mode) {
            case 'adunaic':
            case 'blackspeech':
            case 'quenya':
            case 'sindarin':
            case 'sindarin-beleriand':
            case 'telerin':
            case 'westron':
                return import(`glaemscribe/js/modes/${mode}.glaem.js`);
        }
    }
}
