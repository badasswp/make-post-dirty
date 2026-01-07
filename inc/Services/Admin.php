<?php
/**
 * Admin Service.
 *
 * This service manages the admin area of the
 * plugin. It provides functionality for registering
 * the plugin options/settings.
 *
 * @package MakePostDirty
 */

namespace MakePostDirty\Services;

use MakePostDirty\Abstracts\Service;
use MakePostDirty\Interfaces\Kernel;

class Admin extends Service implements Kernel {
	/**
	 * Plugin Option.
	 *
	 * @var string
	 */
	const PLUGIN_SLUG = 'make-post-dirty';

	/**
	 * Plugin Option.
	 *
	 * @var string
	 */
	const PLUGIN_OPTION = 'make_post_dirty';

	/**
	 * Plugin Group.
	 *
	 * @var string
	 */
	const PLUGIN_GROUP = 'make-post-dirty-group';

	/**
	 * Plugin Section.
	 *
	 * @var string
	 */
	const PLUGIN_SECTION = 'make-post-dirty-section';

	/**
	 * Default Post Title.
	 *
	 * @var string
	 */
	const MAKE_POST_DIRTY_TITLE = 'title';

	/**
	 * Default Post Title.
	 *
	 * @var string
	 */
	const MAKE_POST_DIRTY_CONTENT = 'content';

	/**
	 * Default Post Title.
	 *
	 * @var string
	 */
	const MAKE_POST_DIRTY_RANDOM = 'random';

	/**
	 * Default Animation Enable.
	 *
	 * @var string
	 */
	const MAKE_POST_DIRTY_ANIMATION_ENABLE = 'animation_enable';

	/**
	 * Default Post Title.
	 *
	 * @var string
	 */
	const DEFAULT_POST_TITLE = 'Lorem ipsum dolor sit amet...';

	/**
	 * Default Post Content.
	 *
	 * @var string
	 */
	const DEFAULT_POST_CONTENT = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vestibulum at nulla vitae rutrum. Nunc purus nulla, tincidunt sed turpis in, ullamcorper commodo libero.';

	/**
	 * Plugin Options.
	 *
	 * @var mixed[]
	 */
	public array $options;

	/**
	 * Bind to WP.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'register_options_page' ] );
		add_action( 'admin_init', [ $this, 'register_options_init' ] );
	}

	/**
	 * Register Options Page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_page(): void {
		add_menu_page(
			esc_html__( 'Make Post Dirty', 'make-post-dirty' ),
			esc_html__( 'Make Post Dirty', 'make-post-dirty' ),
			'manage_options',
			self::PLUGIN_SLUG,
			[ $this, 'register_options_cb' ],
			'data:image/svg+xml;base64,' . base64_encode(
				'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
					<path d="m6.249 11.065.44-.44h3.186l-1.5 1.5H7.31l-1.957 1.96A.792.792 0 0 1 4 13.524V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v1.5L12.5 8V5.5h-7v6.315l.749-.75ZM20 19.75H7v-1.5h13v1.5Zm0-12.653-8.967 9.064L8 17l.867-2.935L17.833 5 20 7.097Z" />
				</svg>'
			),
			100
		);
	}

	/**
	 * Register Options Callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_cb(): void {
		$this->options = get_option( self::PLUGIN_OPTION, [] );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Make Post Dirty', 'make-post-dirty' ); ?></h1>
			<p><?php esc_html_e( 'A useful tool for populating the block editor title and content.', 'make-post-dirty' ); ?></p>
			<form method="post" action="options.php">
			<?php
				settings_fields( self::PLUGIN_GROUP );
				do_settings_sections( self::PLUGIN_SLUG );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register Options Init.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_options_init(): void {
		register_setting(
			self::PLUGIN_GROUP,
			self::PLUGIN_OPTION,
			[ $this, 'sanitize_options' ]
		);

		foreach ( $this->get_sections() as $section ) {
			add_settings_section(
				$section['name'] ?? '',
				$section['label'] ?? '',
				null,
				self::PLUGIN_SLUG
			);
		}

		foreach ( $this->get_options() as $option ) {
			if ( ! isset( $option['name'] ) || ! isset( $option['cb'] ) || ! is_callable( $option['cb'] ) ) {
				continue;
			}

			add_settings_field(
				$option['name'] ?? '',
				$option['label'] ?? '',
				$option['cb'],
				$option['page'] ?? '',
				$option['section'] ?? ''
			);
		}
	}

	/**
	 * Get Form Sections.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_sections(): array {
		return [
			[
				'name'  => self::PLUGIN_SECTION,
				'label' => esc_html__( 'Settings', 'make-post-dirty' ),
			],
		];
	}

	/**
	 * Get Callback name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Form Control name.
	 * @return string
	 */
	protected function get_callback_name( $name ): string {
		return sprintf( '%s_cb', $name );
	}

	/**
	 * Get Plugin Options.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[]
	 */
	protected function get_options(): array {
		$options = [
			[
				'name'    => self::MAKE_POST_DIRTY_TITLE,
				'label'   => esc_html__( 'Post Title', 'make-post-dirty' ),
				'cb'      => [ $this, $this->get_callback_name( self::MAKE_POST_DIRTY_TITLE ) ],
				'page'    => self::PLUGIN_SLUG,
				'section' => self::PLUGIN_SECTION,
			],
			[
				'name'    => self::MAKE_POST_DIRTY_CONTENT,
				'label'   => esc_html__( 'Post Content', 'make-post-dirty' ),
				'cb'      => [ $this, $this->get_callback_name( self::MAKE_POST_DIRTY_CONTENT ) ],
				'page'    => self::PLUGIN_SLUG,
				'section' => self::PLUGIN_SECTION,
			],
			[
				'name'    => self::MAKE_POST_DIRTY_RANDOM,
				'label'   => esc_html__( 'Use Random Post', 'make-post-dirty' ),
				'cb'      => [ $this, $this->get_callback_name( self::MAKE_POST_DIRTY_RANDOM ) ],
				'page'    => self::PLUGIN_SLUG,
				'section' => self::PLUGIN_SECTION,
			],
			[
				'name'    => self::MAKE_POST_DIRTY_ANIMATION_ENABLE,
				'label'   => esc_html__( 'Animation Enable', 'make-post-dirty' ),
				'cb'      => [ $this, $this->get_callback_name( self::MAKE_POST_DIRTY_ANIMATION_ENABLE ) ],
				'page'    => self::PLUGIN_SLUG,
				'section' => self::PLUGIN_SECTION,
			],
		];

		/**
		 * Filter Option Fields.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed[] $options Option Fields.
		 * @return mixed[]
		 */
		return apply_filters( 'make_post_dirty_admin_fields', $options );
	}

	/**
	 * Title Callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function title_cb(): void {
		printf(
			'<input
			   type="text"
			   id="%2$s"
			   name="%1$s[%2$s]"
			   placeholder="%4$s"
			   value="%3$s"
			   class="wide"
		   />',
			esc_attr( self::PLUGIN_OPTION ),
			esc_attr( self::MAKE_POST_DIRTY_TITLE ),
			esc_attr( $this->options[ self::MAKE_POST_DIRTY_TITLE ] ?? '' ),
			esc_attr( self::DEFAULT_POST_TITLE )
		);
	}

	/**
	 * Content Callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function content_cb(): void {
		printf(
			'<textarea
				id="%2$s"
				name="%1$s[%2$s]"
				rows="5"
				cols="50"
				placeholder="%4$s"
			>%3$s</textarea>',
			esc_attr( self::PLUGIN_OPTION ),
			esc_attr( self::MAKE_POST_DIRTY_CONTENT ),
			esc_attr( $this->options[ self::MAKE_POST_DIRTY_CONTENT ] ?? '' ),
			esc_attr( self::DEFAULT_POST_CONTENT )
		);
	}

	/**
	 * Random Callback.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function random_cb(): void {
		printf(
			'<input
				type="checkbox"
				id="%2$s"
				name="%1$s[%2$s]"
				value="1" %3$s
			/>',
			esc_attr( self::PLUGIN_OPTION ),
			esc_attr( self::MAKE_POST_DIRTY_RANDOM ),
			checked( 1, $this->options[ self::MAKE_POST_DIRTY_RANDOM ] ?? 0, false )
		);
	}

	/**
	 * Animation Enable.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function animation_enable_cb(): void {
		printf(
			'<input
				type="checkbox"
				id="%2$s"
				name="%1$s[%2$s]"
				value="1" %3$s
			/>',
			esc_attr( self::PLUGIN_OPTION ),
			esc_attr( self::MAKE_POST_DIRTY_ANIMATION_ENABLE ),
			checked( 1, $this->options[ self::MAKE_POST_DIRTY_ANIMATION_ENABLE ] ?? 0, false )
		);
	}

	/**
	 * Sanitize Options.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed[] $input Plugin Options.
	 * @return mixed[]
	 */
	public function sanitize_options( $input ): array {
		$sanitized_options = [];

		if ( isset( $input[ self::MAKE_POST_DIRTY_TITLE ] ) ) {
			$input_data = trim( (string) $input[ self::MAKE_POST_DIRTY_TITLE ] );

			$sanitized_options[ self::MAKE_POST_DIRTY_TITLE ] = sanitize_text_field( $input_data );
		}

		if ( isset( $input[ self::MAKE_POST_DIRTY_CONTENT ] ) ) {
			$input_data = trim( (string) $input[ self::MAKE_POST_DIRTY_CONTENT ] );

			$sanitized_options[ self::MAKE_POST_DIRTY_CONTENT ] = sanitize_textarea_field( $input_data );
		}

		if ( isset( $input[ self::MAKE_POST_DIRTY_RANDOM ] ) ) {
			$input_data = trim( (string) $input[ self::MAKE_POST_DIRTY_RANDOM ] );

			$sanitized_options[ self::MAKE_POST_DIRTY_RANDOM ] = absint( $input_data );
		}

		if ( isset( $input[ self::MAKE_POST_DIRTY_ANIMATION_ENABLE ] ) ) {
			$input_data = trim( (string) $input[ self::MAKE_POST_DIRTY_ANIMATION_ENABLE ] );

			$sanitized_options[ self::MAKE_POST_DIRTY_ANIMATION_ENABLE ] = absint( $input_data );
		}

		return $sanitized_options;
	}

	/**
	 * Get Settings.
	 *
	 * Ensure graceful fallback for unset values
	 * when plugin is first installed.
	 *
	 * @return mixed[]
	 */
	public static function get_settings(): array {
		$settings = get_option( self::PLUGIN_OPTION, [] );

		if ( empty( $settings[ self::MAKE_POST_DIRTY_TITLE ] ) ) {
			$settings[ self::MAKE_POST_DIRTY_TITLE ] = self::DEFAULT_POST_TITLE;
		}

		if ( empty( $settings[ self::MAKE_POST_DIRTY_CONTENT ] ) ) {
			$settings[ self::MAKE_POST_DIRTY_CONTENT ] = self::DEFAULT_POST_CONTENT;
		}

		if ( empty( $settings[ self::MAKE_POST_DIRTY_RANDOM ] ) ) {
			$settings[ self::MAKE_POST_DIRTY_RANDOM ] = '';
		}

		return apply_filters(
			'make_post_dirty_settings',
			[
				self::MAKE_POST_DIRTY_TITLE   => $settings[ self::MAKE_POST_DIRTY_TITLE ] ?? '',
				self::MAKE_POST_DIRTY_CONTENT => $settings[ self::MAKE_POST_DIRTY_CONTENT ] ?? '',
				self::MAKE_POST_DIRTY_RANDOM  => $settings[ self::MAKE_POST_DIRTY_RANDOM ] ?? '',
			]
		);
	}
}
