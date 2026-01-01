import { test, expect } from '@wordpress/e2e-test-utils-playwright';

export async function createNewPost( page ) {
	await page.goto( '/wp-admin/post-new.php' );
	await page.waitForSelector( '.edit-post-layout' );
}

test.describe( 'Make Post Dirty', () => {
	test.beforeEach( async ( { page } ) => {
		createNewPost( page );
	} );

	test( 'displays the plugin icon', async ( { page } ) => {
		const pluginIcon = page.getByTestId( 'make-post-dirty-btn' );

		await expect( pluginIcon ).toBeVisible();
	} );

	test( 'uses custom plugin options', async ( { page } ) => {
		await page.getByRole( 'link', { name: 'View Posts' } ).click();
		await expect(
			page.getByRole( 'link', { name: 'Posts', exact: true } )
		).toBeVisible();

		// Go to plugin options page.
		await page.getByRole( 'link', { name: 'Make Post Dirty' } ).click();
		await expect(
			page.getByRole( 'heading', { name: 'Make Post Dirty' } )
		).toBeVisible();

		const postTitleField = page.getByRole( 'textbox', {
			name: 'Lorem ipsum dolor sit amet...',
		} );
		const postContentField = page.getByRole( 'textbox', {
			name: 'Lorem ipsum dolor sit amet,',
		} );

		await expect( postTitleField ).toBeVisible();
		await postTitleField.fill( 'Hello World' );

		await expect( postContentField ).toBeVisible();
		await postContentField.fill( 'Welcome to WordPress.' );

		// Save plugin options.
		await page.getByRole( 'button', { name: 'Save Changes' } ).click();

		// Now create a new post.
		await page.getByRole( 'link', { name: 'Posts', exact: true } ).click();
		await page
			.getByLabel( 'Main menu', { exact: true } )
			.getByRole( 'link', { name: 'Add Post' } )
			.click();

		const pluginIcon = page.getByTestId( 'make-post-dirty-btn' );

		await expect( pluginIcon ).toBeVisible();

		await pluginIcon.click();
		await page.waitForTimeout( 1000 );

		const postTitle = page
			.locator( 'iframe[name="editor-canvas"]' )
			.contentFrame()
			.getByRole( 'textbox', { name: 'Add title' } );

		const postContent = page
			.locator( 'iframe[name="editor-canvas"]' )
			.contentFrame()
			.getByText( 'Welcome to WordPress.' );

		await expect( postTitle ).toBeVisible();
		await expect( postTitle ).toHaveText( 'Hello World' );

		await expect( postContent ).toBeVisible();
		await expect( postContent ).toHaveText( 'Welcome to WordPress.' );
	} );

	test( 'populates block editor with default values', async ( { page } ) => {
		await page.getByRole( 'link', { name: 'View Posts' } ).click();
		await expect(
			page.getByRole( 'link', { name: 'Posts', exact: true } )
		).toBeVisible();

		// Go to plugin options page.
		await page.getByRole( 'link', { name: 'Make Post Dirty' } ).click();
		await expect(
			page.getByRole( 'heading', { name: 'Make Post Dirty' } )
		).toBeVisible();

		const postTitleField = page.getByRole( 'textbox', {
			name: 'Lorem ipsum dolor sit amet...',
		} );
		const postContentField = page.getByRole( 'textbox', {
			name: 'Lorem ipsum dolor sit amet,',
		} );

		await expect( postTitleField ).toBeVisible();
		await postTitleField.fill( '' );

		await expect( postContentField ).toBeVisible();
		await postContentField.fill( '' );

		// Save plugin options.
		await page.getByRole( 'button', { name: 'Save Changes' } ).click();

		// Now create a new post.
		await page.getByRole( 'link', { name: 'Posts', exact: true } ).click();
		await page
			.getByLabel( 'Main menu', { exact: true } )
			.getByRole( 'link', { name: 'Add Post' } )
			.click();

		const pluginIcon = page.getByTestId( 'make-post-dirty-btn' );

		await expect( pluginIcon ).toBeVisible();

		await pluginIcon.click();
		await page.waitForTimeout( 1000 );

		const postTitle = page
			.locator( 'iframe[name="editor-canvas"]' )
			.contentFrame()
			.getByRole( 'textbox', { name: 'Add title' } );

		const postContent = page
			.locator( 'iframe[name="editor-canvas"]' )
			.contentFrame()
			.getByText( 'Lorem ipsum dolor sit amet,' );

		await expect( postTitle ).toBeVisible();
		await expect( postTitle ).toHaveText( 'Lorem ipsum dolor sit amet...' );

		await expect( postContent ).toBeVisible();
		await expect( postContent ).toHaveText(
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vestibulum at nulla vitae rutrum. Nunc purus nulla, tincidunt sed turpis in, ullamcorper commodo libero.'
		);
	} );
} );
