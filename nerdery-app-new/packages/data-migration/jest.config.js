module.exports = {
    preset: 'ts-jest',
    testEnvironment: 'node',
    roots: ['<rootDir>/src'],
    testMatch: ['**/*.test.ts'],
    transform: {
      '^.+\\.ts$': 'ts-jest',
    },
    collectCoverageFrom: [
      'src/**/*.ts',
      '!src/**/*.d.ts',
      '!src/__tests__/**',
      '!src/index.ts', // Exclude main entry point from coverage
    ],
    coverageDirectory: 'coverage',
    coverageReporters: ['text', 'lcov', 'html'],
    //setupFilesAfterEnv: ['<rootDir>/src/__tests__/setup.ts'],
    testTimeout: 10000,
    verbose: true,
      moduleNameMapper: {
      // Alias '@shared' to the shared-utils package
      '^@shared/(.*)$': '<rootDir>/../shared/$1',
    },
  };