/* eslint-disable @typescript-eslint/no-unsafe-member-access */
/* eslint-disable no-undef */
/// <reference path="../_types/glaemscribe.d.ts" />

import { ITranscriber } from '@root/components/Tengwar._types';
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
export default class Transcriber implements ITranscriber {
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

    public async getModeName(mode: string) {
        const params = await this.initializeGlaemscribe(mode);
        if (params === null) {
            return null;
        }

        const loadedMode = Glaemscribe.resource_manager.loaded_modes[params.mode];
        return loadedMode.human_name;
    }

    public async initializeGlaemscribe(mode: string) {
        let transcriber = this._getGlaemscribe();
        if (! transcriber) {
            transcriber = await this._loadGlaemscribe();
        }

        let humanName: string;
        if (! (mode in transcriber.resource_manager.loaded_modes)) {
            mode = await this._loadMode(mode);

            if (mode === null) {
                return null;
            }

            transcriber.resource_manager.load_modes(mode);
            humanName = transcriber.resource_manager.loaded_modes[mode].human_name;
        }

        const charset = DefaultGlaemscribeCharacterSet;
        if (! (charset in transcriber.resource_manager.loaded_charsets)) {
            await this._loadCharset(charset);
            transcriber.resource_manager.load_charsets(charset);
        }

        return {
            charset,
            humanName,
            mode,
        };
    }

    private _getGlaemscribe() {
        return (window as any).Glaemscribe as IGlaemscribe;
    }

    private _setGlaemscribe(transcriber: IGlaemscribe) {
        (window as any).Glaemscribe = transcriber;
    }

    private async _loadGlaemscribe(): Promise<IGlaemscribe> {
        // Load and execute Glaemscribe
        const module = await import('glaemscribe/js/glaemscribe.min.js');
        // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
        this._setGlaemscribe(module.Glaemscribe);
        return this._getGlaemscribe();
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
