

/**
 * Transcribes the specified text body to tengwar using the parmaite font.
 * @param {string} text 
 * @param {string} mode 
 */
export const transcribe = (text, mode) => {
    if (!window.EDTengwarInitialized || !window.EDTengwarInitialized.hasOwnProperty(mode)) {
        Glaemscribe.resource_manager.load_modes(mode);

        let initialized = window.EDTengwarInitialized || {};
        initialized[mode] = true;

        window.EDTengwarInitialized = initialized;
    }

    const trascriber = Glaemscribe.resource_manager.loaded_modes[mode];
    const charset = Glaemscribe.resource_manager.loaded_charsets['tengwar_ds_parmaite'];
    if (!trascriber) {
        return undefined;
    }
    
    // Transcribe using Glaemscribe transcriber for the specified mode
    // The result is an array with three elements: 
    // 0th element: whether the transcription was successful (true/false)
    // 1th element: transcription result
    // 2th element: debug data
    const result = trascriber.transcribe(text, charset);
    if (!result[0]) {
        return undefined; // failed!
    }

    // Return the transcription results
    return result[1].trim();
};
