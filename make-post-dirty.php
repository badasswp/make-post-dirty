<?php
/**
 * Plugin Name: Make Post Dirty
 * Plugin URI:  https://github.com/badasswp/make-post-dirty
 * Description: A useful tool for populating the block editor title and content.
 * Version:     1.2.0
 * Author:      badasswp
 * Author URI:  https://github.com/badasswp
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: make-post-dirty
 * Domain Path: /languages
 *
 * @package MakePostDirty
 */

namespace badasswp\MakePostDirty;

if ( ! defined( 'WPINC' ) ) {
	exit;
}

define( 'MAKE_POST_DIRTY_AUTOLOAD', __DIR__ . '/vendor/autoload.php' );

// Composer Check.
if ( ! file_exists( MAKE_POST_DIRTY_AUTOLOAD ) ) {
	add_action(
		'admin_notices',
		function () {
			vprintf(
				/* translators: Plugin directory path. */
				esc_html__( 'Fatal Error: Composer not setup in %s', 'make-post-dirty' ),
				[ __DIR__ ]
			);
		}
	);

	return;
}

// Run Plugin.
require_once MAKE_POST_DIRTY_AUTOLOAD;
( \MakePostDirty\Plugin::get_instance() )->run();
