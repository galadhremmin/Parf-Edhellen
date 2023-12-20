module.exports = {
  preset: 'ts-jest',
  testEnvironment: 'jsdom',
  rootDir: 'resources/assets/ts',
  testMatch: [
    '**/*._spec.ts?(x)',
  ],
  transform: {
    '^.+\\.[tj]sx?$': [
      '@swc/jest',
      {
        jsc: {
          transform: {
            react: {
              runtime: 'automatic',
            },
          }
        }
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
