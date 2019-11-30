/// <reference path="../_types/glaemscribe.d.ts" />
import {
    DefaultGlaemscribeCharacterSet,
    GlaemscribeModeMappings,
} from '../config';

const enum InitializationContext {
    Charset = 'charset',
    Mode = 'mode',
}

/**
 * Transcribes the specified text body to tengwar using the parmaite font.
 * @param {string} text
 * @param {string} mode
 */
export default class Transcriber {
    private static _initializations: { [key: string]: Promise<string> } = {};

    public async transcribe(text: string, mode: string) {
        const params = await this.initializeGlaemscribe(mode);
        if (params === null) {
            return undefined; // fail!
        }

        const transcriber = Glaemscribe.resource_manager.loaded_modes[params.mode];
        const charset = Glaemscribe.resource_manager.loaded_charsets[params.charset];
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

        if (! transcriber.resource_manager.loaded_modes.hasOwnProperty(mode)) {
            mode = await this._loadMode(mode);

            if (mode === null) {
                return null;
            }

            transcriber.resource_manager.load_modes(mode);
        }

        const charset = DefaultGlaemscribeCharacterSet;
        if (! transcriber.resource_manager.loaded_charsets.hasOwnProperty(charset)) {
            await this._loadCharset(charset);
            transcriber.resource_manager.load_charsets(charset);
        }

        return {
            charset,
            mode,
        };
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

    private async _loadCharset(charset: string) {
        try {
            return await this._initialize(InitializationContext.Charset, charset, async () => {
                await import(`glaemscribe/js/charsets/${charset}.cst.js`);
                return charset;
            });
        } catch (ex) {
            throw new Error(`${charset} is not included in the current application bundle: ${ex}.`);
        }
    }

    private async _loadMode(mode: string) {
        const actualMode = GlaemscribeModeMappings[mode] || mode;
        try {
            return await this._initialize(InitializationContext.Mode, mode, async () => {
                await import(`glaemscribe/js/modes/${actualMode}.glaem.js`);
                return actualMode;
            });
        } catch (ex) {
            return null;
        }
    }

    private async _initialize(context: InitializationContext, name: string, callback: () => Promise<string>) {
        const key = [context, name].join('-');
        const existingInitialization = Transcriber._initializations[key];
        if (existingInitialization !== undefined) {
            return existingInitialization;
        }

        const promise = callback().finally(() => {
            delete Transcriber._initializations[key];
        });

        Transcriber._initializations[key] = promise;
        return promise;
    }
}
