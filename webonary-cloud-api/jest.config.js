module.exports = {
  modulePaths: ['<rootDir>'],
  roots: ['<rootDir>/lib/__tests__', '<rootDir>/lambda/__tests__'],
  testEnvironment: 'node',
  coverageThreshold: {
    global: {
      lines: 85,
      branches: 85,
      statements: 85,
      functions: 85,
    },
  },
  testMatch: ['**/*.test.ts'],
  transform: {
    '^.+\\.ts?$': 'ts-jest',
  },
};
