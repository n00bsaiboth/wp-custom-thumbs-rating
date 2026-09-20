<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thumbs_rating_scripts' ) ) :
	function thumbs_rating_scripts() {
		wp_enqueue_script(
			'thumbs_rating_scripts',
			THUMBS_RATING_URL . 'assets/js/general.js',
			array(),
			THUMBS_RATING_VERSION,
			true
		);

		wp_localize_script(
			'thumbs_rating_scripts',
			'thumbs_rating_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'thumbs-rating-nonce' ),
			)
		);
	}
	add_action( 'wp_enqueue_scripts', 'thumbs_rating_scripts' );
endif;

if ( ! function_exists( 'thumbs_rating_styles' ) ) :
	function thumbs_rating_styles() {
		wp_register_style(
			'thumbs_rating_styles',
			THUMBS_RATING_URL . 'assets/css/style.css',
			array(),
			THUMBS_RATING_VERSION
		);
		wp_enqueue_style( 'thumbs_rating_styles' );
	}
	add_action( 'wp_enqueue_scripts', 'thumbs_rating_styles' );
endif;