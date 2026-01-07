<?php

namespace MakePostDirty\Tests\Services;

use WP_Mock;
use Mockery;
use WP_Mock\Tools\TestCase;
use MakePostDirty\Services\Admin;

/**
 * @covers \MakePostDirty\Services\Admin::register
 * @covers \MakePostDirty\Services\Admin::register_options_page
 * @covers \MakePostDirty\Services\Admin::register_options_cb
 * @covers \MakePostDirty\Services\Admin::register_options_init
 * @covers \MakePostDirty\Services\Admin::get_sections
 * @covers \MakePostDirty\Services\Admin::get_callback_name
 * @covers \MakePostDirty\Services\Admin::get_options
 * @covers \MakePostDirty\Services\Admin::title_cb
 * @covers \MakePostDirty\Services\Admin::content_cb
 * @covers \MakePostDirty\Services\Admin::random_cb
 * @covers \MakePostDirty\Services\Admin::sanitize_options
 * @covers \MakePostDirty\Services\Admin::get_settings
 */
class AdminTest extends TestCase {
	public function setUp(): void {
		WP_Mock::setUp();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_register() {
		$admin = new Admin();

		WP_Mock::expectActionAdded( 'admin_menu', [ $admin, 'register_options_page' ] );
		WP_Mock::expectActionAdded( 'admin_init', [ $admin, 'register_options_init' ] );

		$register = $admin->register();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_register_options_page() {
		$admin = new Admin();

		WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		WP_Mock::userFunction( 'add_menu_page' )
			->with(
				'Make Post Dirty',
				'Make Post Dirty',
				'manage_options',
				'make-post-dirty',
				[ $admin, 'register_options_cb' ],
				'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iY3VycmVudENvbG9yIj4KCQkJCQk8cGF0aCBkPSJtNi4yNDkgMTEuMDY1LjQ0LS40NGgzLjE4NmwtMS41IDEuNUg3LjMxbC0xLjk1NyAxLjk2QS43OTIuNzkyIDAgMCAxIDQgMTMuNTI0VjVhMSAxIDAgMCAxIDEtMWg4YTEgMSAwIDAgMSAxIDF2MS41TDEyLjUgOFY1LjVoLTd2Ni4zMTVsLjc0OS0uNzVaTTIwIDE5Ljc1SDd2LTEuNWgxM3YxLjVabTAtMTIuNjUzLTguOTY3IDkuMDY0TDggMTdsLjg2Ny0yLjkzNUwxNy44MzMgNSAyMCA3LjA5N1oiIC8+CgkJCQk8L3N2Zz4=',
				100
			)
			->andReturn( null );

		$register = $admin->register_options_page();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_register_options_cb() {
		$admin = new Admin();

		WP_Mock::userFunction( 'get_option' )
			->with( 'make_post_dirty', [] )
			->andReturn( [] );

		WP_Mock::userFunction( 'esc_html_e' )
			->andReturnUsing(
				function ( $arg ) {
					echo $arg;
				}
			);

		WP_Mock::userFunction( 'settings_fields' )
			->andReturnUsing(
				function ( $arg ) {
					?>
					<section id="<?php echo $arg; ?>"></section>
					<?php
				}
			);

		WP_Mock::userFunction( 'do_settings_sections' )
			->andReturnUsing(
				function ( $arg ) {
					?>
					<div id="<?php echo $arg; ?>"></div>
					<?php
				}
			);

		WP_Mock::userFunction( 'submit_button' )
			->andReturnUsing(
				function () {
					?>
					<button type="submit">Save Changes</button>
					<?php
				}
			);

		$register = $admin->register_options_cb();

		$this->expectOutputString(
			'		<div class="wrap">
			<h1>Make Post Dirty</h1>
			<p>A useful tool for populating the block editor title and content.</p>
			<form method="post" action="options.php">
								<section id="make-post-dirty-group"></section>
										<div id="make-post-dirty"></div>
										<button type="submit">Save Changes</button>
								</form>
		</div>
		'
		);
		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_register_options_init() {
		$admin = new Admin();

		WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		WP_Mock::userFunction( 'register_setting' )
			->with(
				'make-post-dirty-group',
				'make_post_dirty',
				[ $admin, 'sanitize_options' ]
			)
			->andReturn( null );

		WP_Mock::userFunction( 'add_settings_section' )
			->once()
			->with(
				'make-post-dirty-section',
				'Settings',
				null,
				'make-post-dirty'
			)
			->andReturn( null );

		WP_Mock::expectFilter(
			'make_post_dirty_admin_fields',
			[
				[
					'name'    => 'title',
					'label'   => 'Post Title',
					'cb'      => [ $admin, 'title_cb' ],
					'page'    => 'make-post-dirty',
					'section' => 'make-post-dirty-section',
				],
				[
					'name'    => 'content',
					'label'   => 'Post Content',
					'cb'      => [ $admin, 'content_cb' ],
					'page'    => 'make-post-dirty',
					'section' => 'make-post-dirty-section',
				],
				[
					'name'    => 'random',
					'label'   => 'Use Random Post',
					'cb'      => [ $admin, 'random_cb' ],
					'page'    => 'make-post-dirty',
					'section' => 'make-post-dirty-section',
				],
				[
					'name'    => 'animation_enable',
					'label'   => 'Animation Enable',
					'cb'      => [ $admin, 'animation_enable_cb' ],
					'page'    => 'make-post-dirty',
					'section' => 'make-post-dirty-section',
				],
			]
		);

		WP_Mock::userFunction( 'add_settings_field' )
			->times( 4 );

		$register = $admin->register_options_init();

		$this->assertNull( $register );
		$this->assertConditionsMet();
	}

	public function test_get_sections() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$sections = $admin->get_sections();

		$this->assertSame(
			$sections,
			[
				[
					'name'  => 'make-post-dirty-section',
					'label' => 'Settings',
				],
			]
		);
	}

	public function test_get_callback_name() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$this->assertSame(
			'name-of-control_cb',
			$admin->get_callback_name( 'name-of-control' )
		);
	}

	public function test_get_options() {
		$admin = Mockery::mock( Admin::class )->makePartial();
		$admin->shouldAllowMockingProtectedMethods();

		$options = [
			[
				'name'    => 'title',
				'label'   => 'Post Title',
				'cb'      => [ $admin, 'title_cb' ],
				'page'    => 'make-post-dirty',
				'section' => 'make-post-dirty-section',
			],
			[
				'name'    => 'content',
				'label'   => 'Post Content',
				'cb'      => [ $admin, 'content_cb' ],
				'page'    => 'make-post-dirty',
				'section' => 'make-post-dirty-section',
			],
			[
				'name'    => 'random',
				'label'   => 'Use Random Post',
				'cb'      => [ $admin, 'random_cb' ],
				'page'    => 'make-post-dirty',
				'section' => 'make-post-dirty-section',
			],
			[
				'name'    => 'animation_enable',
				'label'   => 'Animation Enable',
				'cb'      => [ $admin, 'animation_enable_cb' ],
				'page'    => 'make-post-dirty',
				'section' => 'make-post-dirty-section',
			],
		];

		WP_Mock::userFunction( 'esc_html__' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		WP_Mock::expectFilter(
			'make_post_dirty_admin_fields',
			$options
		);

		$this->assertSame( $options, $admin->get_options() );
		$this->assertConditionsMet();
	}

	public function test_title_cb() {
		WP_Mock::userFunction( 'esc_attr' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$response = ( new Admin() )->title_cb();

		$this->expectOutputString(
			'<input
			   type="text"
			   id="title"
			   name="make_post_dirty[title]"
			   placeholder="Lorem ipsum dolor sit amet..."
			   value=""
			   class="wide"
		   />'
		);
		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_content_cb() {
		WP_Mock::userFunction( 'esc_attr' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$response = ( new Admin() )->content_cb();

		$this->expectOutputString(
			'<textarea
				id="content"
				name="make_post_dirty[content]"
				rows="5"
				cols="50"
				placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vestibulum at nulla vitae rutrum. Nunc purus nulla, tincidunt sed turpis in, ullamcorper commodo libero."
			></textarea>'
		);
		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_random_cb() {
		$admin = new Admin();

		WP_Mock::userFunction( 'esc_attr' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		WP_Mock::userFunction( 'checked' )
			->andReturnUsing(
				function ( $arg1, $arg2, $arg3 ) {
					return $arg1 === $arg2 ? 'checked' : '';
				}
			);

		$admin->options['random'] = 1;

		$response = $admin->random_cb();

		$this->expectOutputString(
			'<input
				type="checkbox"
				id="random"
				name="make_post_dirty[random]"
				value="1" checked
			/>'
		);
		$this->assertNull( $response );
		$this->assertConditionsMet();
	}

	public function test_get_settings_uses_default_values_if_plugin_options_not_set() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'make_post_dirty', [] )
			->andReturn( [] );

		WP_Mock::expectFilter(
			'make_post_dirty_settings',
			[
				'title'   => 'Lorem ipsum dolor sit amet...',
				'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vestibulum at nulla vitae rutrum. Nunc purus nulla, tincidunt sed turpis in, ullamcorper commodo libero.',
				'random'  => '',
			]
		);

		$response = Admin::get_settings();

		$this->assertSame(
			$response,
			[
				'title'   => 'Lorem ipsum dolor sit amet...',
				'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vestibulum at nulla vitae rutrum. Nunc purus nulla, tincidunt sed turpis in, ullamcorper commodo libero.',
				'random'  => '',
			]
		);
	}

	public function test_get_settings_returns_plugin_options_if_set() {
		WP_Mock::userFunction( 'get_option' )
			->with( 'make_post_dirty', [] )
			->andReturn(
				[
					'title'   => 'Hello World',
					'content' => 'Lorem ipsum dolor sit amet...',
					'random'  => true,
				]
			);

		WP_Mock::expectFilter(
			'make_post_dirty_settings',
			[
				'title'   => 'Hello World',
				'content' => 'Lorem ipsum dolor sit amet...',
				'random'  => true,
			]
		);

		$response = Admin::get_settings();

		$this->assertSame(
			$response,
			[
				'title'   => 'Hello World',
				'content' => 'Lorem ipsum dolor sit amet...',
				'random'  => true,
			]
		);
	}

	public function test_sanitize_options_does_not_sanitize_any_control_if_not_set() {
		$sanitized_options = ( new Admin() )->sanitize_options( [] );

		$this->assertSame( $sanitized_options, [] );
		$this->assertConditionsMet();
	}

	public function test_sanitize_options_sanitizes_only_controls_that_are_set() {
		WP_Mock::userFunction( 'sanitize_textarea_field' )
			->andReturnUsing(
				function ( $arg ) {
					return $arg;
				}
			);

		$sanitized_options = ( new Admin() )->sanitize_options(
			[
				'content' => 'Lorem ipsum dolor sit amet...',
			]
		);

		$this->assertSame(
			$sanitized_options,
			[
				'content' => 'Lorem ipsum dolor sit amet...',
			]
		);
		$this->assertConditionsMet();
	}
}
