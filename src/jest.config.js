/** @type {import('ts-jest').JestConfigWithTsJest} */

module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  rootDir: 'resources/assets/ts',
  testMatch: [
    '**/*._spec.ts?(x)',
  ],
  transform: {
    '^.+\\.[tj]sx?$': [
      'ts-jest',
      {
        tsconfig: 'tsconfig.unit-test.json',
      },
    ],
  },
  moduleNameMapper: {
    '^@root/(.*)$': '<rootDir>/$1',
    '\\.(css|scss)$': 'identity-obj-proxy',
  },
  transformIgnorePatterns: [
    "<rootDir>/node_modules/(?!sinon)"
  ],
};
