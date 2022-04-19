module.exports = {
    "env": {
        "browser": true,
        "es6": true
    },
    "extends": [
        "plugin:@typescript-eslint/recommended",
        "plugin:@typescript-eslint/recommended-requiring-type-checking",
        "prettier",
        "eslint:recommended",
        "plugin:react/recommended",
        "plugin:react/jsx-runtime"
    ],
    "parser": "@typescript-eslint/parser",
    "parserOptions": {
        "project": "tsconfig.json",
        "sourceType": "module"
    },
    "plugins": [
        "eslint-plugin-jsdoc",
        "eslint-plugin-prefer-arrow",
        "@typescript-eslint",
        "@typescript-eslint/tslint"
    ],
    "rules": {
        "no-unused-vars": 0,
        "@typescript-eslint/restrict-template-expressions": 0,
        "@typescript-eslint/no-unsafe-assignment": 0,
        "@typescript-eslint/restrict-template-expressions": 0,
        "@typescript-eslint/triple-slash-reference": 0, // For Glaemscribe
        "@typescript-eslint/no-explicit-any": 1,
        "@typescript-eslint/no-unsafe-return": 1,
        "@typescript-eslint/no-unsafe-call": 1,
        "@typescript-eslint/no-unsafe-argument": 1,
        "@typescript-eslint/restrict-plus-operands": 1,
        "@typescript-eslint/no-misused-promises": 1,
        "@typescript-eslint/no-floating-promises": 1,
        "react/no-unescaped-entities": 0
    },
    "ignorePatterns": ["*._spec.ts", "*._spec.tsx", "*._types.ts", "*.d.ts"],
    "settings": {
        "react": {
            "createClass": "createReactClass",
            "pragma": "React",
            "version": "detect",
        },
        "propWrapperFunctions": [
            // The names of any function used to wrap propTypes, e.g. `forbidExtraProps`. If this isn't set, any propTypes wrapped in a function will be skipped.
            "forbidExtraProps",
            {"property": "freeze", "object": "Object"},
            {"property": "myFavoriteWrapper"},
            // for rules that check exact prop wrappers
            {"property": "forbidExtraProps", "exact": true}
        ]
    },
    "parserOptions": {
        "project": ["./tsconfig.json"]
    }
};
