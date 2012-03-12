(function (definition) {

    if (typeof define !== "undefined") {
        define(definition);
    } else if (typeof require !== "undefined") {
        definition(require, exports, module);
    } else {
        definition(void 0, tengwar = {});
    }

})(function (require, exports, module) {

// king's letter, general use
var mode = {
    "names": [
        ["tinco", "parma", "calma", "quesse"],
        ["ando", "umbar", "anga", "ungwe"],
        ["thule", "formen", "harma", "hwesta"],
        ["anto", "ampa", "anca", "unque"],
        ["numen", "malta", "noldo", "nwalme"],
        ["ore", "vala", "anna", "wilya"],
        ["romen", "arda", "lambe", "alda"],
        ["silme", "silme-nuquerna", "esse", "esse-nuquerna"],
        ["hyarmen", "hwesta-sindarinwa", "yanta", "ure"],
        ["halle", "short-carrier", "long-carrier", "round-carrier"],
        ["tinco-extended", "parma-extended", "calma-extended", "quesse-extended"],
    ],
    "aliases": {
        "vilya": "wilya"
    },
    // classical
    "tengwar": {
        // 1
        "tinco": "1", // t
        "parma": "q", // p
        "calma": "a", // c
        "quesse": "z", // qu
        // 2
        "ando" : "2", // nd
        "umbar": "w", // mb
        "anga" : "s", // ng
        "ungwe": "x", // ngw
        // 3
        "thule" : "3", // th
        "formen": "e", // ph / f
        "harma" : "d", // h / ch
        "hwesta": "c", // hw / chw
        // 4
        "anto" : "4", // nt
        "ampa" : "r", // mp
        "anca" : "f", // nc
        "unque": "v", // nqu
        // 5
        "numen" : "5", // n
        "malta" : "t", // m
        "noldo" : "g", // ng
        "nwalme": "b", // ngw / nw
        // 6
        "ore"  : "6", // r
        "vala" : "y", // v
        "anna" : "h", // -
        "wilya": "n", // w / v
        // 7
        "romen": "7", // medial r
        "arda" : "u", // rd / rh
        "lambe": "j", // l
        "alda" : "m", // ld / lh
        // 8
        "silme":          "8", // s
        "silme-nuquerna": "i", // s
        "esse":           "k", // z
        "esse-nuquerna":  ",", // z
        // 9
        "hyarmen":           "9", // hyarmen
        "hwesta-sindarinwa": "o", // hwesta sindarinwa
        "yanta":             "l", // yanta
        "ure":               ".", // ure
        // 10
        "halle": "½", // halle
        "short-carrier": "`",
        "long-carrier": "~",
        "round-carrier": "]",
        // I
        "tinco-extended": "!",
        "parma-extended": "Q",
        "calma-extended": "A",
        "quesse-extended": "Z",
        // punctuation
        "comma": "=",
        "full-stop": "-",
        "exclamation-point": "Á",
        "question-mark": "À",
        "open-paren": "&#140;",
        "close-paren": "&#156;",
        "flourish-left": "&#286;",
        "flourish-right": "&#287;",
    },
    "tehtar": {
        "a": "#EDC",
        "e": "$RFV",
        "i": "%TGB",
        "o": "^YHN",
        "u": [
            "&",
            "U",
            "J",
            "M",
            "&#256;", // backward hooks, from the alt font to the custom font
            "&#257;",
            "&#258;",
            "&#259;"
        ],
        //"á": "",
        "ó": [
            "&#260;",
            "&#261;",
            "&#262;",
            "&#263;"
        ],
        "ú": [
            "&#264;",
            "&#265;",
            "&#266;",
            "&#267;"
        ],
        "í": [
            "&#212;",
            "&#213;",
            "&#214;",
            "&#215;",
        ],
        "w": "èéêë",
        "y": "ÌÍÎÏ´",
        /*
        "o-under": [
            "ä",
            "&#229;", // a ring above
            "æ",
            "ç",
            "|"
        ],
        */
        // TODO deal with the fact that all of these
        // should only be final (for word spacing) except
        // for the first S-hook for "calma" and "quesse"
        // since they appear within the tengwa
        "s": {
            "special": true,
            "tinco": "+",
            "ando": "+",
            "numen": "+",
            "lambe": "_",
            "quesse": "|",
            "short-carrier": "}",
        },
        "s-inverse": {
            "special": true,
            "tinco": "¡"
        },
        "s-extended": {
            "special": true,
            "tinco": "&#199;"
        },
        "s-flourish": {
            "special": true,
            "tinco": "&#163;",
            "lambe": "&#165;"
        },
        "tilde-above": "Pp",
        "tilde-below": [
            ":",
            ";",
            "&#176;",
        ],
        "tilde-high-above": ")0",
        "tilde-far-below": "?/",
        "bar-above": "{[",
        "bar-below": [
            '"',
            "'",
            "&#184;" // cedilla
        ],
        "bar-high-above": "ìî",
        "bar-far-below": "íï"
    },
    "barsAndTildes": [
        "tilde-above",
        "tilde-below",
        "tilde-high-above",
        "tilde-far-below",
        "bar-above",
        "bar-below",
        "bar-high-above",
        "bar-high-below"
    ],
    "tehtaPositions": {
        "tinco": {
            "o": 3,
            "w": 3,
            "others": 2
        },
        "parma": {
            "o": 3,
            "w": 3,
            "others": 2
        },
        "calma": {
            "o": 3,
            "w": 3,
            "u": 3,
            "others": 2
        },
        "quesse": {
            "o": 3,
            "w": 3,
            "others": 2
        },
        "ando": {
            "wide": true,
            "e": 1,
            "o": 2,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "umbar": {
            "wide": true,
            "e": 1,
            "o": 2,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "anga": {
            "wide": true,
            "e": 1,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "ungwe": {
            "wide": true,
            "e": 1,
            "o": 1,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "thule": {
            "others": 3
        },
        "formen": 3,
        "harma": {
            "e": 0,
            "o": 3,
            "u": 7,
            "ó": 2,
            "ú": 2,
            "w": 0,
            "others": 1
        },
        "hwesta": {
            "e": 0,
            "o": 3,
            "u": 7,
            "w": 0,
            "others": 1
        },
        "anto": {
            "wide": true,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "ampa": {
            "wide": true,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "anca": {
            "wide": true,
            "u": 7,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "unque": {
            "wide": true,
            "u": 7,
            "others": 0
        },
        "numen": {
            "wide": true,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "malta": {
            "wide": true,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "noldo": {
            "wide": true,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "nwalme": {
            "wide": true,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "ore": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 3,
            "ú": 3,
            "others": 1
        },
        "vala": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 3,
            "ú": 3,
            "others": 1
        },
        "anna": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 2,
            "ú": 2,
            "others": 1
        },
        "wilya": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 3,
            "ú": 3,
            "others": 1
        },
        "romen": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 2,
            "ú": 2,
            "y": null,
            "others": 1
        },
        "arda": {
            "a": 1,
            "e": 3,
            "i": 1,
            "o": 3,
            "u": 3,
            "í": 1,
            "ó": 2,
            "ú": 2,
            "y": null,
            "others": 0
        },
        "lambe": {
            "wide": true,
            "e": 1,
            "y": 4,
            "ó": 1,
            "ú": 1,
            "others": 0
        },
        "alda": {
            "wide": true,
            "others": 1
        },
        "silme": {
            "y": 3,
            "others": null
        }, 
        "silme-nuquerna": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 3,
            "ú": 3,
            "y": null,
            "others": 1
        },
        "esse": {
            "y": null,
            "others": null
        },
        "esse-nuquerna": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 3,
            "ú": 3,
            "others": 1
        },
        "hyarmen": 3,
        "hwesta-sindarinwa": {
            "o": 2,
            "u": 2,
            "ó": 1,
            "ú": 2,
            "others": 0
        },
        "yanta": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 2,
            "ú": 2,
            "others": 1
        },
        "ure": {
            "e": 3,
            "o": 3,
            "u": 3,
            "ó": 3,
            "ú": 3,
            "others": 1
        },
        // should not occur:
        "halle": {
            "others": null
        },
        "short-carrier": 3,
        "long-carrier": {
            "y": null,
            "others": 3
        },
        "round-carrier": 3,
        "tinco-extended": 3,
        "parma-extended": 3,
        "calma-extended": {
            "o": 3,
            "u": 7,
            "ó": 2,
            "ú": 2,
            "others": 1
        },
        "quesse-extended": {
            "o": 0,
            "u": 7,
            "others": 1
        }
    },
    "substitutions": {
        "k": "c",
        "x": "cs",
        "qu": "cw",
        "q": "cw",
        "ë": "e",
        "â": "á",
        "ê": "é",
        "î": "í",
        "ô": "ó",
        "û": "ú"
    },
    "transcriptions": {

        // consonants
        "t": "tinco",
        "nt": "tinco:tilde-above",
        "tt": "tinco:tilde-below",

        "p": "parma",
        "mp": "parma:tilde-above",
        "pp": "parma:tilde-below",

        "ch'": "calma", // ch is palatal fricative, as in bach
        "nch'": "calma:tilde-above",

        "c": "quesse",
        "nc": "quesse:tilde-above",

        "d": "ando",
        "nd": "ando:tilde-above",
        "dd": "ando:tilde-below",

        "b": "umbar",
        "mb": "umbar:tilde-above",
        "bb": "umbar:tilde-below",

        "j": "anca",
        "nj": "anca:tilde-above",

        "g": "ungwe",
        "ng": "ungwe:tilde-above",
        "gg": "ungwe:tilde-below",

        "th": "thule",
        "nth": "thule:tilde-above",

        "f": "formen",
        "ph": "formen",
        "mf": "formen:tilde-above",
        "mph": "formen:tilde-above",

        "sh": "harma",

        "h": "hyarmen",
        "ch": "hwesta",
        "hw": "hwesta-sindarinwa",
        "wh": "hwesta-sindarinwa",

        "gh": "unque",
        "ngh": "unque:tilde-above",

        "dh": "anto",
        "ndh": "anto:tilde-above",

        "v": "ampa",
        "bh": "ampa",
        "mv": "ampa:tilde-above",
        "mbh": "ampa:tilde-above",

        "n": "numen",
        "nn": "numen:tilde-above",

        "m": "malta",
        "mm": "malta:tilde-above",

        "ng": "nwalme",
        "ñ": "nwalme",
        "nwal": "nwalme:w;lambe:a",

        "r": "romen",
        "rr": "romen:tilde-below",
        "rh": "arda",

        "l": "lambe",
        "ll": "lambe:tilde-below",
        "lh": "alda",

        "s": "silme",
        "ss": "silme:tilde-below",

        "z": "esse",

        "á": "wilya:a",
        "é": "long-carrier:e",
        "í": "long-carrier:i",
        "ó": "long-carrier:o",
        "ú": "long-carrier:u",
        "w": "vala",

        "ai": "anna:a",
        "oi": "anna:o",
        "ui": "anna:u",
        "au": "vala:a",
        "eu": "vala:e",
        "iu": "vala:i",
        "ae": "yanta:a",

    },
    "vowelTranscriptions": {

        "a": "short-carrier:a",
        "e": "short-carrier:e",
        "i": "short-carrier:i",
        "o": "short-carrier:o",
        "u": "short-carrier:u",

        "á": "wilya:a",
        "é": "long-carrier:e",
        "í": "long-carrier:i",
        "ó": "short-carrier:ó",
        "ú": "short-carrier:ú",

        "w": "vala",
        "y": "short-carrier:í"

    },

    "words": {
        "iant": "yanta;tinco:tilde-above:a",
        "iaur": "yanta;vala:a;ore",
        "baranduiniant": "umbar;romen:a;ando:tilde-above:a;anna:u;yanta;anto:tilde-above:a",
        "ioreth": "yanta;romen:o;thule:e",
        "noldo": "nwalme;lambe:o;ando;short-carrier:o",
        "noldor": "nwalme;lambe:o;ando;ore:o",
        "is": "short-carrier:i:s"
    },

    "punctuation": {
        "-": "comma",
        ",": "comma",
        ":": "comma",
        ";": "full-stop",
        ".": "full-stop",
        "!": "exclamation-point",
        "?": "question-mark",
        "(": "open-paren",
        ")": "close-paren",
        ">": "flourish-left",
        "<": "flourish-right"
    },

    "annotations": {
        "tinco": {"tengwa": "t"},
        "parma": {"tengwa": "p"},
        "calma": {"tengwa": "c"},
        "quesse": {"tengwa": "c"},
        "ando": {"tengwa": "d"},
        "umbar": {"tengwa": "b"},
        "anga": {"tengwa": "ch"},
        "ungwe": {"tengwa": "g"},
        "thule": {"tengwa": "th"},
        "formen": {"tengwa": "f"},
        "hyarmen": {"tengwa": "h"},
        "hwesta": {"tengwa": "kh"},
        "unque": {"tengwa": "gh"},
        "anto": {"tengwa": "dh"},
        "anca": {"tengwa": "j"},
        "ampa": {"tengwa": "v"},
        "numen": {"tengwa": "n"},
        "malta": {"tengwa": "m"},
        "nwalme": {"tengwa": "ñ"},
        "romen": {"tengwa": "r"},
        "ore": {"tengwa": "-r"},
        "lambe": {"tengwa": "l"},
        "silme": {"tengwa": "s"},
        "silme-nuquerna": {"tengwa": "s"},
        "esse": {"tengwa": "z"},
        "esse-nuquerna": {"tengwa": "z"},
        "harma": {"tengwa": "sh"},
        "alda": {"tengwa": "lh"},
        "arda": {"tengwa": "rh"},
        "wilya": {"tengwa": "a"},
        "vala": {"tengwa": "w"},
        "anna": {"tengwa": "i"},
        "vala": {"tengwa": "w"},
        "yanta": {"tengwa": "e"},
        "hwesta-sindarinwa": {"tengwa": "wh"},
        "s": {"following": "s"},
        "s-inverse": {"following": "s<sub>2</sub>"},
        "s-extended": {"following": "s<sub>3</sub>"},
        "s-flourish": {"following": "s<sub>4</sub>"},
        "long-carrier": {"tengwa": "´"},
        "short-carrier": {},
        "tilde-above": {"above": "nmñ-"},
        "tilde-below": {"below": "2"},
        "a": {"tehta-above": "a"},
        "e": {"tehta-above": "e"},
        "i": {"tehta-above": "i"},
        "o": {"tehta-above": "o"},
        "u": {"tehta-above": "u"},
        "ó": {"tehta-above": "ó"},
        "ú": {"tehta-above": "ú"},
        "í": {"tehta-above": "y"},
        "y": {"tehta-below": "y"},
        "w": {"tehta-above": "w"},
        "full-stop": {"tengwa": "."},
        "exclamation-point": {"tengwa": "!"},
        "question-mark": {"tengwa": "?"},
        "comma": {"tengwa": "-"},
        "open-paren": {"tengwa": "("},
        "close-paren": {"tengwa": ")"},
        "flourish-left": {"tengwa": "“"},
        "flourish-right": {"tengwa": "”"}
    }
};

function tehtaKeyForTengwa(tengwa, tehta) {
    var positions = mode.tehtaPositions;
    if (mode.tehtar[tehta].special && !mode.tehtar[tehta][tengwa])
        return null;
    if (mode.barsAndTildes.indexOf(tehta) >= 0) {
        if (["lambe", "alda"].indexOf(tengwa) >= 0 && mode.tehtar[tehta].length >= 2)
            return 2;
        return positions[tengwa].wide ? 0 : 1;
    } else if (positions[tengwa] !== undefined) {
        if (positions[tengwa][tehta] !== undefined) {
            return positions[tengwa][tehta];
        } else if (positions[tengwa].others !== undefined) {
            return positions[tengwa].others;
        } else {
            return positions[tengwa];
        }
    }
    return 0;
}

function tehtaForTengwa(tengwa, tehta) {
    var tehtaKey = tehtaKeyForTengwa(tengwa, tehta);
    if (tehtaKey === null) 
        return null;
    return (
        mode.tehtar[tehta][tengwa] ||
        mode.tehtar[tehta][tehtaKey] ||
        ""
    );
}

function tengwaTehtaPairDisplay(tengwa, tehta) {
    var tehta = tehtaForTengwa(tengwa, tehta);
    if (tehta === null) {
        return (
            "<span style=\"color: #ddd\">" +
            mode.tengwar[tengwa] +
            "</span>"
        );
    } else {
        return mode.tengwar[tengwa] + tehta;
    }
}

exports.displayTengwarTehtar = displayTengwarTehtar;
function displayTengwarTehtar() {
    document.body.innerHTML = Object.keys(mode.tehtar).map(function (tehta) {
        return "<table align=\"left\"><caption>" + tehta + "</caption>" +
        mode.names.map(function (row) {
            return "<tr><td>" + row.map(function (name) {
                return (
                    "<span class=\"tengwar\">" +
                    tengwaTehtaPairDisplay(name, tehta) +
                    "</span>"
                );
            }).join("</td><td>") + "</td></tr>";
        }).join("</tr><tr>") + "</tr></table>";
    }).join(" ");
}

var transcriptionsRe = new RegExp("^([aeiouóú]?'?)(" +
    Object.keys(mode.transcriptions).sort(function (a, b) {
        return b.length - a.length;
    }).join("|") +
")(w?)(y?)(s?)('*)", "ig");
var vowelTranscriptionsRe = new RegExp("^(" + 
    Object.keys(mode.vowelTranscriptions).join("|") +
")", "ig");
var substitutionsRe = new RegExp("(" +
    Object.keys(mode.substitutions).join("|") +
")", "ig");

function transcribeWordToEncoding(latin) {
    latin = latin
    .toLowerCase()
    .replace(substitutionsRe, function ($, key) {
        return mode.substitutions[key];
    });
    if (mode.words[latin])
        return mode.words[latin];
    var parts = [];
    var length;
    var first = true;
    var maybeFinal;
    while (latin.length) {
        if (latin[0] != "s")
            maybeFinal = undefined;
        length = latin.length;
        latin = latin
        .replace(transcriptionsRe, function ($, vowel, tengwa, w, y, s, prime) {
            //console.log(latin, [vowel, tengwa, w, y, s]);
            w = w || ""; s = s || ""; y = y || "";
            var value = mode.transcriptions[tengwa];
            tengwa = value.split(":")[0];
            var tehtar = value.split(":").slice(1).join(":");
            var voweled = value.split(":").filter(function (term) {
                return mode.vowelTranscriptions[term];
            }).length;
            if (vowel) {
                if (!voweled) {
                    // flip if necessary
                    if (
                        tehtaForTengwa(tengwa, vowel) === null &&
                        tehtaForTengwa(tengwa + "-nuquerna", vowel) !== null
                    ) {
                        value = [tengwa + "-nuquerna"]
                        .concat(tehtar)
                        .concat([vowel])
                        .filter(function (part) {
                            return part;
                        }).join(":");
                    } else {
                        value += ":" + vowel;
                    }
                } else {
                    parts.push(transcribeWordToEncoding(vowel));
                }
                voweled = true;
            }
            if (w && !voweled) {
                value += ":w";
                w = "";
                voweled = true;
            }
            if (y) {
                value += ":y";
                y = "";
            }
            // must go last because it has a non-zero width
            if (s && !w) {
                var length = prime.length;
                var possibilities = [
                    "s",
                    "s-inverse",
                    "s-extended",
                    "s-flourish"
                ].filter(function (tehta) {
                    return tehtaForTengwa(tengwa, tehta);
                });
                while (possibilities.length && length) {
                    possibilities.shift();
                    length--;
                }
                if (possibilities.length) {
                    if (value.split(":").indexOf("quesse") >= 0) {
                        value = value + ":" + possibilities.shift();
                        s = "";
                    } else {
                        maybeFinal = value + ":" + possibilities.shift();
                    }
                }
            }
            parts.push(value);
            first = false;
            return w + y + s;
        });
        if (length === latin.length) {
            length = latin.length;
            latin = latin.replace(vowelTranscriptionsRe, function ($, vowel) {
                var value = mode.vowelTranscriptions[vowel];
                parts.push(value);
                return "";
            });
            if (length === latin.length) {
                //throw new Error("Can't transcribe " + latin.slice(1));
                if (mode.punctuation[latin[0]])
                    parts.push(mode.punctuation[latin[0]]);
                latin = latin.slice(1);
            }
        }
    }
    if (parts.length) {
        if (maybeFinal && parts[parts.length - 1] == "silme") {
            parts.pop();
            parts.pop();
            parts.push(maybeFinal);
        }
        parts.push(parts.pop().replace("romen", "ore"));
    }
    /*
    * failed attempt to distinguish yanta spelling of consonantal i
    * automatically in iant, iaur, and ioreth but not in galadriel,
    * moria
    parts = parts.map(function (part, i) {
        if (i === parts.length - 1)
            return part;
        if (part !== "short-carrier:i")
            return part;
        if (parts[i + 1].split(":").filter(function (term) {
            return term === "a";
        }).length) {
            return "yanta";
        } else {
            return part;
        }
    });
    */
    /*
    // abandoned trick to replace "is" with short-carrier with s hook
    parts = parts.map(function (part, i) {
        //console.log(part);
        if (part === "silme-nuquerna:i")
            return "short-carrier:s";
        return part;
    });
    */
    return parts.join(";");
}

exports.transcribeToEncoding = transcribeToEncoding;
function transcribeToEncoding(latin) {
    latin = latin.replace(/[,:] +/g, ",");
    return latin.split(/\n\n\n+/).map(function (section) {
        return section.split(/\n\n/).map(function (paragraph) {
            return paragraph.split(/\n/).map(function (stanza) {
                return stanza.split(/\s+/).map(function (word) {
                    var parts = []
                    word.replace(/([\wáéíóúÁÉÍÓÚëËâêîôûÂÊÎÔÛ']+)|(\W+)/g, function ($, word, others) {
                        if (word) {
                            parts.push(transcribeWordToEncoding(word));
                        } else {
                            parts.push(transcribeWordToEncoding(others));
                        }
                    });
                    return parts.join(";");
                }).join(" ");
            }).join("\n");
        }).join("\n\n");
    }).join("\n\n\n");
}

function decodeToFontHtml(transcription) {
    return transcription.split(/\n\n\n+/).map(function (section) {
        return section.split(/\n\n/).map(function (paragraph) {
            return "<p>" + paragraph.split(/\n/).map(function (stanza) {
                return stanza.split(/\s+/).map(function (word) {
                    return word.split(";").filter(function (vertical) {
                        return vertical;
                    }).map(function (vertical) {
                        var parts = vertical.split(":");
                        var tengwa = parts[0];
                        var tehtar = parts.slice(1);
                        return mode.tengwar[tengwa] + tehtar.map(function (tehta) {
                            return tehta ? tehtaForTengwa(tengwa, tehta) : "";
                        }).join("");
                    }).join("");
                }).join(" ");
            }).join("<br>") + "</p>";
        }).join("\n\n");
    }).join("\n\n\n");
}

function decodeToFont(transcription) {
    return transcription.split(/\n\n\n+/).map(function (section) {
        return section.split(/\n\n/).map(function (paragraph) {
            return paragraph.split(/\n/).map(function (stanza) {
                return stanza.split(/\s+/).map(function (word) {
                    return word.split(";").filter(function (vertical) {
                        return vertical;
                    }).map(function (vertical) {
                        var parts = vertical.split(":");
                        var tengwa = parts[0];
                        var tehtar = parts.slice(1);
                        return mode.tengwar[tengwa] + tehtar.map(function (tehta) {
                            return tehta ? tehtaForTengwa(tengwa, tehta) : "";
                        }).join("");
                    }).join("");
                }).join(" ");
            }).join("\n");
        }).join("\n\n");
    }).join("\n\n\n");
}

exports.annotate = annotate;
function annotate(latin) {
    return transcribeToEncoding(latin).split(/\s+/).map(function (word) {
        return word.split(";").map(function (word) {
            var form = {};
            word.split(":").forEach(function (part) {
                var annotation = mode.annotations[part];
                for (var name in annotation) {
                    form[name] = "<abbr title=\"" + part + " (" + name + ")\">" + annotation[name] + "</abbr>";
                }
            });
            var middle = [];
            if (form.tengwa) {
                middle.push("<strong>" + form.tengwa + "</strong>");
            }
            if (form.following) {
                middle.push(" -" + form.following);
            }
            return [
                form['tehta-above'],
                form['above'],
                middle.join(""),
                form['below'],
                form['tehta-below']
            ];
        });
    });
}

exports.annotateHtml = annotateHtml;
function annotateHtml(latin) {
    return annotate(latin).map(function (word) {
        var table = [[], [], [], [], []];
        var i = 0;
        word.forEach(function (cluster) {
            cluster.forEach(function (note, j) {
                table[j][i] = note;
            });
            i++;
        });
        return "<table align=\"left\">" + table.map(function (row) {
            return "<tr>" + row.map(function (cell) {
                return "<td>" + (cell || "&nbsp;") + "</td>";
            }).join("") + "</tr>";
        }).join("") + "</table>";
    }).join("");
}

exports.transcribe = transcribe;
function transcribe(latin) {
    return decodeToFont(transcribeToEncoding(latin));
}

exports.transcribeHtml = transcribeHtml;
function transcribeHtml(latin) {
    return decodeToFontHtml(transcribeToEncoding(latin));
}

if (typeof jQuery !== "undefined") {
    jQuery.fn.tengwar = function () {
        this.each(function () {
            var that = jQuery(this);
            that
            .html(transcribe(that.html()))
            .addClass("tengwar");
        });
    };
}

});
