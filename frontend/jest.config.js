const nextJest = require('next/jest');

const createJestConfig = nextJest({
    // Provide the path to your Next.js app to load next.config.js and .env files
    dir: './',
});

// Custom Jest configuration
const customConfig = {
    // Setup files to run before each test
    setupFilesAfterEnv: ['<rootDir>/jest.setup.js'],

    // Test environment
    testEnvironment: 'jsdom',

    // Module name mapper for path aliases (matches tsconfig.json paths)
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/$1',
    },

    // Test file patterns
    testMatch: [
        '<rootDir>/tests/**/*.test.{ts,tsx,js,jsx}',
        '<rootDir>/**/__tests__/**/*.{ts,tsx,js,jsx}',
    ],

    // Coverage configuration
    collectCoverageFrom: [
        'app/**/*.{ts,tsx}',
        'components/**/*.{ts,tsx}',
        'services/**/*.{ts,tsx}',
        '!**/*.d.ts',
        '!**/node_modules/**',
    ],
};

// Create and export Jest config
module.exports = createJestConfig(customConfig);
