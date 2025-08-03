/**
 * Jest setup file for JavaScript tests
 */

import "@testing-library/jest-dom";

// Mock WordPress globals
global.wp = {
  ajax: {
    post: jest.fn(),
    send: jest.fn(),
  },
  i18n: {
    __: jest.fn((text) => text),
    _x: jest.fn((text) => text),
    _n: jest.fn((single, plural, number) => (number === 1 ? single : plural)),
  },
  hooks: {
    addAction: jest.fn(),
    addFilter: jest.fn(),
    doAction: jest.fn(),
    applyFilters: jest.fn(),
  },
};

// Mock jQuery
global.$ = global.jQuery = jest.fn(() => ({
  ready: jest.fn(),
  on: jest.fn(),
  off: jest.fn(),
  trigger: jest.fn(),
  find: jest.fn(() => ({
    length: 0,
    each: jest.fn(),
    val: jest.fn(),
    text: jest.fn(),
    html: jest.fn(),
    addClass: jest.fn(),
    removeClass: jest.fn(),
    toggleClass: jest.fn(),
    show: jest.fn(),
    hide: jest.fn(),
    fadeIn: jest.fn(),
    fadeOut: jest.fn(),
    slideUp: jest.fn(),
    slideDown: jest.fn(),
  })),
  val: jest.fn(),
  text: jest.fn(),
  html: jest.fn(),
  addClass: jest.fn(),
  removeClass: jest.fn(),
  toggleClass: jest.fn(),
  show: jest.fn(),
  hide: jest.fn(),
  fadeIn: jest.fn(),
  fadeOut: jest.fn(),
  slideUp: jest.fn(),
  slideDown: jest.fn(),
  each: jest.fn(),
  click: jest.fn(),
  submit: jest.fn(),
  change: jest.fn(),
  keyup: jest.fn(),
  keydown: jest.fn(),
  focus: jest.fn(),
  blur: jest.fn(),
  serialize: jest.fn(),
  serializeArray: jest.fn(),
  ajax: jest.fn(),
  post: jest.fn(),
  get: jest.fn(),
}));

// Mock console methods for cleaner test output
global.console = {
  ...console,
  log: jest.fn(),
  warn: jest.fn(),
  error: jest.fn(),
};

// Mock localStorage
const localStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
};
global.localStorage = localStorageMock;

// Mock sessionStorage
const sessionStorageMock = {
  getItem: jest.fn(),
  setItem: jest.fn(),
  removeItem: jest.fn(),
  clear: jest.fn(),
};
global.sessionStorage = sessionStorageMock;

// Mock window.location
delete window.location;
window.location = {
  href: "http://localhost",
  origin: "http://localhost",
  protocol: "http:",
  host: "localhost",
  hostname: "localhost",
  port: "",
  pathname: "/",
  search: "",
  hash: "",
  assign: jest.fn(),
  replace: jest.fn(),
  reload: jest.fn(),
};

// Mock window.history
window.history = {
  pushState: jest.fn(),
  replaceState: jest.fn(),
  back: jest.fn(),
  forward: jest.fn(),
  go: jest.fn(),
};

// Mock fetch
global.fetch = jest.fn(() =>
  Promise.resolve({
    ok: true,
    status: 200,
    json: () => Promise.resolve({}),
    text: () => Promise.resolve(""),
    blob: () => Promise.resolve(new Blob()),
  })
);

// Mock IntersectionObserver
global.IntersectionObserver = jest.fn(() => ({
  observe: jest.fn(),
  disconnect: jest.fn(),
  unobserve: jest.fn(),
}));

// Mock ResizeObserver
global.ResizeObserver = jest.fn(() => ({
  observe: jest.fn(),
  disconnect: jest.fn(),
  unobserve: jest.fn(),
}));

// Mock MutationObserver
global.MutationObserver = jest.fn(() => ({
  observe: jest.fn(),
  disconnect: jest.fn(),
  takeRecords: jest.fn(),
}));

// Mock requestAnimationFrame
global.requestAnimationFrame = jest.fn((cb) => setTimeout(cb, 0));
global.cancelAnimationFrame = jest.fn((id) => clearTimeout(id));

// Mock performance
global.performance = {
  now: jest.fn(() => Date.now()),
  mark: jest.fn(),
  measure: jest.fn(),
  getEntriesByName: jest.fn(() => []),
  getEntriesByType: jest.fn(() => []),
};

// Setup DOM environment
document.body.innerHTML = `
  <div id="test-container"></div>
`;

// Clean up after each test
afterEach(() => {
  // Clear all mocks
  jest.clearAllMocks();

  // Reset DOM
  document.body.innerHTML = '<div id="test-container"></div>';

  // Clear localStorage and sessionStorage
  localStorageMock.clear();
  sessionStorageMock.clear();
});

// Global test utilities
global.testUtils = {
  createMockElement: (tag = "div", attributes = {}) => {
    const element = document.createElement(tag);
    Object.keys(attributes).forEach((key) => {
      element.setAttribute(key, attributes[key]);
    });
    return element;
  },

  createMockEvent: (type, properties = {}) => {
    const event = new Event(type, { bubbles: true, cancelable: true });
    Object.keys(properties).forEach((key) => {
      event[key] = properties[key];
    });
    return event;
  },

  waitFor: (condition, timeout = 1000) => {
    return new Promise((resolve, reject) => {
      const startTime = Date.now();
      const check = () => {
        if (condition()) {
          resolve();
        } else if (Date.now() - startTime > timeout) {
          reject(new Error("Timeout waiting for condition"));
        } else {
          setTimeout(check, 10);
        }
      };
      check();
    });
  },
};
