/**
 * Transcribes text to tengwar with Glaemscribe, for the PHP side, which has no transcriber of
 * its own. It follows the phrase form (utilities/Glaemscribe.ts): the same modes, the same
 * character set, one transcription per fragment.
 *
 * stdin:  {"items": [{"mode": "quenya-tengwar-classical", "text": "márië"}, …]}
 * stdout: {"tengwar": ["…", null, …]} -- null where the text could not be transcribed.
 */
const fs = require('fs');
const path = require('path');
const vm = require('vm');

// Mirrors DefaultGlaemscribeCharacterSet and GlaemscribeModeMappings in resources/assets/ts/config.ts.
const CHARSET = 'tengwar_guni_annatar';
const MODE_MAPPINGS = {
    'blackspeech': 'blackspeech-tengwar-general_use',
    'quenya': 'quenya-tengwar-classical',
    'sindarin': 'sindarin-tengwar-general_use',
    'sindarin-beleriand': 'sindarin-tengwar-beleriand',
    'telerin': 'telerin-tengwar-glaemscrafu',
    'westron': 'westron-tengwar-glaemscrafu',
};

const root = path.join(path.dirname(require.resolve('glaemscribe/package.json')), 'js');

// The resource files are browser scripts that assign to a global Glaemscribe.
globalThis.window = globalThis;
const run = (file) => vm.runInThisContext(fs.readFileSync(path.join(root, file), 'utf8'), { filename: file });

run('glaemscribe.min.js');
const manager = globalThis.Glaemscribe.resource_manager;

run(`charsets/${CHARSET}.cst.js`);
manager.load_charsets(CHARSET);
const charset = manager.loaded_charsets[CHARSET];

const loadMode = (name) => {
    const mode = MODE_MAPPINGS[name] || name;
    if (! (mode in manager.loaded_modes)) {
        try {
            run(`modes/${mode}.glaem.js`);
            manager.load_modes(mode);
        } catch (e) {
            return null;
        }
    }

    return manager.loaded_modes[mode] || null;
};

const { items } = JSON.parse(fs.readFileSync(0, 'utf8'));
const tengwar = items.map(({ mode, text }) => {
    const transcriber = mode ? loadMode(mode) : null;
    if (! transcriber || ! text) {
        return null;
    }

    const [ok, result] = transcriber.transcribe(text, charset);
    return ok ? result.trim() : null;
});

process.stdout.write(JSON.stringify({ tengwar }));
