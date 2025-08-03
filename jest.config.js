module.exports = {
  testEnvironment: "jsdom",
  setupFilesAfterEnv: ["<rootDir>/tests/js/setup.js"],
  testMatch: [
    "<rootDir>/tests/js/**/*.test.js",
    "<rootDir>/tests/js/**/*.spec.js",
  ],
  collectCoverageFrom: [
    "assets/js/**/*.js",
    "src/js/**/*.js",
    "!**/node_modules/**",
    "!**/vendor/**",
    "!**/tests/**",
  ],
  coverageDirectory: "tests/coverage/js",
  coverageReporters: ["html", "text", "lcov"],
  moduleNameMapping: {
    "^@/(.*)$": "<rootDir>/assets/js/$1",
  },
  transform: {
    "^.+\\.js$": "babel-jest",
  },
  testTimeout: 10000,
};
