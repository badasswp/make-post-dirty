<?php

namespace MakePostDirty\Tests\Services;

use Mockery;
use WP_Mock\Tools\TestCase;
use MakePostDirty\Services\Asset;

/**
 * @covers \MakePostDirty\Services\Asset::register
 * @covers \MakePostDirty\Services\Asset::register_scripts
 * @covers \MakePostDirty\Services\Asset::register_translation
 * @covers \MakePostDirty\Services\Asset::get_assets
 * @covers \MakePostDirty\Services\Admin::get_settings
 */
class AssetTest extends TestCase {
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_register() {
		$asset = new Asset();

		\WP_Mock::expectActionAdded(
			'init',
			[
				$asset,
				'register_translation',
			]
		);
		\WP_Mock::expectActionAdded( 'enqueue_block_editor_assets', [ $asset, 'register_scripts' ] );

		$response = $asset->register();

		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_register_translation() {
		$asset = new Asset();

		$file_name = new \ReflectionClass( Asset::class );
		$file_name = $file_name->getFileName();

		\WP_Mock::userFunction( 'plugin_basename' )
			->once()
			->with( $file_name )
			->andReturn( '.' );

		\WP_Mock::userFunction( 'load_plugin_textdomain' )
			->once()
			->with( 'make-post-dirty', false, './../../languages' );

		$response = $asset->register_translation();

		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_get_assets_should_return_empty_dependencies_if_file_does_not_exist() {
		$asset = Mockery::mock( Asset::class )->makePartial();
		$asset->shouldAllowMockingProtectedMethods();

		$expected = [
			'version'      => strval( time() ),
			'dependencies' => [],
		];

		$actual = $asset->get_assets( __DIR__ . '/../../dist/non-existing-file.php' );

		$this->assertSame( $expected, $actual );
		$this->assertConditionsMet();
	}

	public function test_get_assets_should_return_dependencies_if_file_exists() {
		$asset = Mockery::mock( Asset::class )->makePartial();
		$asset->shouldAllowMockingProtectedMethods();

		$actual = $asset->get_assets( __DIR__ . '/../../dist/app.asset.php' );

		$this->assertIsArray( $actual );
		$this->assertArrayHasKey( 'dependencies', $actual );
		$this->assertArrayHasKey( 'version', $actual );
		$this->assertNotEmpty( $actual['dependencies'] );
		$this->assertConditionsMet();
	}

	public function test_register_scripts() {
		$asset = Mockery::mock( Asset::class )->makePartial();
		$asset->shouldAllowMockingProtectedMethods();

		\WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'make_post_dirty', [] )
			->andReturn(
				[
					'title'   => 'Test Title',
					'content' => 'Test Content',
					'random'  => true,
				]
			);

		\WP_Mock::expectFilter(
			'make_post_dirty_settings',
			[
				'title'   => 'Test Title',
				'content' => 'Test Content',
				'random'  => true,
			]
		);

		\WP_Mock::userFunction( 'plugin_dir_path' )
			->andReturn( __DIR__ );

		$asset->shouldReceive( 'get_assets' )
			->once()
			->with( __DIR__ . '/../../dist/app.asset.php' )
			->andReturn(
				[
					'dependencies' => [
						'react',
						'wp-components',
						'wp-data',
						'wp-editor',
						'wp-i18n',
						'wp-plugins',
						'wp-primitives',
					],
					'version'      => 'b0ee0547444c42928b08',
				]
			);

		\WP_Mock::userFunction( 'plugins_url' )
			->once()
			->with( 'make-post-dirty/dist/app.js' )
			->andReturn( 'https://example.com/wp-content/plugins/make-post-dirty/dist/app.js' );

		\WP_Mock::userFunction( 'wp_enqueue_script' )
			->once()
			->with(
				'make-post-dirty',
				'https://example.com/wp-content/plugins/make-post-dirty/dist/app.js',
				[
					'react',
					'wp-components',
					'wp-data',
					'wp-editor',
					'wp-i18n',
					'wp-plugins',
					'wp-primitives',
				],
				'b0ee0547444c42928b08',
				false
			);

		\WP_Mock::userFunction( 'wp_localize_script' )
			->once()
			->with(
				'make-post-dirty',
				'makePostDirty',
				[
					'title'     => 'Test Title',
					'content'   => 'Test Content',
					'random'    => true,
					'wpVersion' => null,
				]
			);

		\WP_Mock::userFunction( 'wp_set_script_translations' )
			->once()
			->with(
				'make-post-dirty',
				'make-post-dirty',
				__DIR__ . '../../languages'
			);

		$response = $asset->register_scripts();

		$this->assertNull( $response );
		$this->assertConditionsMet();
	}
}
