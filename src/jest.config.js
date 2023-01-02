/** @type {import('ts-jest').JestConfigWithTsJest} */

module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  rootDir: 'resources/assets/ts',
  testMatch: [
    '**/*._spec.ts?(x)',
  ],
  transform: {
    '^.+\\.tsx?$': [
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
};
