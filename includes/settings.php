<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thumbs_rating_register_settings' ) ) :
	function thumbs_rating_register_settings() {

		register_setting(
			'thumbs_rating_settings_group',
			'thumbs_rating_show_title',
			array(
				'type'              => 'boolean',
				'sanitize_callback' => 'thumbs_rating_sanitize_checkbox',
				'default'           => 1,
			)
		);

		register_setting(
			'thumbs_rating_settings_group',
			'thumbs_rating_title',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => __( 'Did you find this article useful?', 'thumbs-rating' ),
			)
		);

		add_settings_section(
			'thumbs_rating_main_section',
			__( 'Button display', 'thumbs-rating' ),
			'thumbs_rating_main_section_callback',
			'thumbs_rating_settings_page'
		);

		add_settings_field(
			'thumbs_rating_show_title',
			__( 'Show title', 'thumbs-rating' ),
			'thumbs_rating_show_title_field',
			'thumbs_rating_settings_page',
			'thumbs_rating_main_section'
		);

		add_settings_field(
			'thumbs_rating_title',
			__( 'Title text', 'thumbs-rating' ),
			'thumbs_rating_title_field',
			'thumbs_rating_settings_page',
			'thumbs_rating_main_section'
		);
	}

	add_action( 'admin_init', 'thumbs_rating_register_settings' );
endif;

if ( ! function_exists( 'thumbs_rating_sanitize_checkbox' ) ) :
	function thumbs_rating_sanitize_checkbox( $value ) {
		return ( isset( $value ) && $value ) ? 1 : 0;
	}
endif;

if ( ! function_exists( 'thumbs_rating_main_section_callback' ) ) :
	function thumbs_rating_main_section_callback() {
		echo '<p>' . esc_html__( 'Control the text shown above the thumbs up/down buttons.', 'thumbs-rating' ) . '</p>';
	}
endif;

if ( ! function_exists( 'thumbs_rating_show_title_field' ) ) :
	function thumbs_rating_show_title_field() {
		$value = absint( get_option( 'thumbs_rating_show_title', 1 ) );
		?>
		<label>
			<input
				type="checkbox"
				name="thumbs_rating_show_title"
				value="1"
				<?php checked( 1, $value ); ?>
			/>
			<?php esc_html_e( 'Display the title above the buttons.', 'thumbs-rating' ); ?>
		</label>
		<?php
	}
endif;

if ( ! function_exists( 'thumbs_rating_title_field' ) ) :
	function thumbs_rating_title_field() {
		$value = get_option( 'thumbs_rating_title', __( 'Did you find this article useful?', 'thumbs-rating' ) );
		?>
		<input
			type="text"
			name="thumbs_rating_title"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		/>
		<p class="description">
			<?php esc_html_e( 'Shown above the thumbs buttons when "Show title" is enabled.', 'thumbs-rating' ); ?>
		</p>
		<?php
	}
endif;

if ( ! function_exists( 'thumbs_rating_add_settings_page' ) ) :
	function thumbs_rating_add_settings_page() {
		add_options_page(
			__( 'Thumbs Rating Settings', 'thumbs-rating' ),
			__( 'Thumbs Rating', 'thumbs-rating' ),
			'manage_options',
			'thumbs_rating_settings_page',
			'thumbs_rating_render_settings_page'
		);
	}

	add_action( 'admin_menu', 'thumbs_rating_add_settings_page' );
endif;

if ( ! function_exists( 'thumbs_rating_render_settings_page' ) ) :
	function thumbs_rating_render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'thumbs_rating_settings_group' );
				do_settings_sections( 'thumbs_rating_settings_page' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
endif;

if ( ! function_exists( 'thumbs_rating_plugin_action_links' ) ) :
	function thumbs_rating_plugin_action_links( $links ) {
		$url = admin_url( 'options-general.php?page=thumbs_rating_settings_page' );

		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'thumbs-rating' ) . '</a>'
		);

		return $links;
	}

	add_filter( 'plugin_action_links_' . plugin_basename( THUMBS_RATING_FILE ), 'thumbs_rating_plugin_action_links' );
endif;