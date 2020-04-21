module.exports = {
  roots: [
    '<rootDir>/lib/__tests__',
    '<rootDir>/lambda/__tests__',
  ],
  preset: 'ts-jest',
  testEnvironment: 'node',
  coverageThreshold: {
    global: {
      lines: 85,
      branches: 85,
      statements: 85,
      functions: 85,
    },
  },
  testMatch: [ '**/*.test.ts'],
  testPathIgnorePatterns: ['/node_modules/', '.js'],
  transform: {
    '^.+\\.ts?$': 'ts-jest',
  }
}