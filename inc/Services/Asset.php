<?php
/**
 * Asset Service.
 *
 * This service manages the registration and
 * binding of the Asset service.
 *
 * @package MakePostDirty
 */

namespace MakePostDirty\Services;

use MakePostDirty\Abstracts\Service;
use MakePostDirty\Interfaces\Kernel;

class Asset extends Service implements Kernel {
	/**
	 * Asset Slug.
	 *
	 * @var string
	 */
	const SLUG = 'make-post-dirty';

	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'init', [ $this, 'register_translation' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'register_scripts' ] );
	}

	/**
	 * Register Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @wp-hook 'enqueue_block_editor_assets'
	 */
	public function register_scripts() {
		global $wp_version;

		$settings = Admin::get_settings();
		$assets   = $this->get_assets( plugin_dir_path( __FILE__ ) . '/../../dist/app.asset.php' );

		wp_enqueue_script(
			self::SLUG,
			plugin_dir_url( __FILE__ ) . '../../dist/app.js',
			$assets['dependencies'],
			$assets['version'],
			false,
		);

		wp_localize_script(
			self::SLUG,
			'makePostDirty',
			[
				'title'           => $settings['title'] ?? '',
				'content'         => $settings['content'] ?? '',
				'random'          => $settings['random'] ?? '',
				'animationEnable' => (bool) ( $settings['animation_enable'] ?? false ),
				'wpVersion'       => $wp_version,
			]
		);

		wp_set_script_translations(
			self::SLUG,
			self::SLUG,
			plugin_dir_path( __FILE__ ) . '../../languages'
		);
	}

	/**
	 * Add Plugin text translation.
	 *
	 * @since 1.0.0
	 *
	 * @wp-hook 'init'
	 */
	public function register_translation() {
		load_plugin_textdomain(
			self::SLUG,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/../../languages'
		);
	}

	/**
	 * Get Asset dependencies.
	 *
	 * @since 1.5.0
	 *
	 * @param string $path Path to webpack generated PHP asset file.
	 * @return array
	 */
	protected function get_assets( string $path ): array {
		$assets = [
			'version'      => strval( time() ),
			'dependencies' => [],
		];

		if ( ! file_exists( $path ) ) {
			return $assets;
		}

		// phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		$assets = require_once $path;

		return $assets;
	}
}
