import { defineConfig } from '@playwright/test';

export default defineConfig( {
	testDir: './tests/e2e',
	outputDir: './tests/e2e/test-results',
	globalSetup: './playwright.setup.js',
	use: {
		baseURL: 'http://make-post-dirty.localhost:9526',
		headless: true,
		viewport: { width: 1280, height: 720 },
		ignoreHTTPSErrors: true,
		storageState: './tests/e2e/storage/storageState.json',
	},
} );
