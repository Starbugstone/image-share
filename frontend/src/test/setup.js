// Test setup file for Vitest
import { config } from '@vue/test-utils'

// Global test configuration
config.global.stubs = {
  // Stub common components that might not be needed in tests
  'router-link': true,
  'router-view': true
}

// Mock window.matchMedia if not available
if (typeof window !== 'undefined') {
  Object.defineProperty(window, 'matchMedia', {
    writable: true,
    value: vi.fn().mockImplementation(query => ({
      matches: false,
      media: query,
      onchange: null,
      addListener: vi.fn(), // deprecated
      removeListener: vi.fn(), // deprecated
      addEventListener: vi.fn(),
      removeEventListener: vi.fn(),
      dispatchEvent: vi.fn(),
    })),
  })
}
