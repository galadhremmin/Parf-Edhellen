/* eslint-env node */
export function isNodeJs() {
    return typeof process !== 'undefined' && ! process?.versions?.node;
}
