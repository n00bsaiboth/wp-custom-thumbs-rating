<?php
/*
Plugin Name: Thumbs Rating
Plugin URI:  https://wordpress.org/plugins/thumbs-rating/
Description: Add thumbs up/down rating to your content.
Author:      Ricard Torres (modernized)
Version:     3.0.0
Author URI:  http://php.quicoto.com/
Text Domain: thumbs-rating
Requires PHP: 7.4
License:     GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*-----------------------------------------------------------------------------------*/
/* Constants */
/*-----------------------------------------------------------------------------------*/

define( 'THUMBS_RATING_URL', plugin_dir_url( __FILE__ ) );
define( 'THUMBS_RATING_PATH', plugin_dir_path( __FILE__ ) );
define( 'THUMBS_RATING_VERSION', '3.0.0' );


/*-----------------------------------------------------------------------------------*/
/* Init / Localization */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_init' ) ) :
	function thumbs_rating_init() {
		load_plugin_textdomain( 'thumbs-rating', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	add_action( 'plugins_loaded', 'thumbs_rating_init' );
endif;


/*-----------------------------------------------------------------------------------*/
/* Enqueue Scripts */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_scripts' ) ) :
	function thumbs_rating_scripts() {
		wp_enqueue_script(
			'thumbs_rating_scripts',
			THUMBS_RATING_URL . 'js/general.js',
			array( 'jquery' ),
			THUMBS_RATING_VERSION,
			true // in footer
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


/*-----------------------------------------------------------------------------------*/
/* Enqueue Styles */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_styles' ) ) :
	function thumbs_rating_styles() {
		wp_register_style( 'thumbs_rating_styles', THUMBS_RATING_URL . 'css/style.css', array(), THUMBS_RATING_VERSION );
		wp_enqueue_style( 'thumbs_rating_styles' );
	}
	add_action( 'wp_enqueue_scripts', 'thumbs_rating_styles' );
endif;


/*-----------------------------------------------------------------------------------*/
/* Render the thumbs up/down buttons */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_getlink' ) ) :
	function thumbs_rating_getlink( $post_ID = 0, $type_of_vote = 0 ) {

		$post_ID = intval( $post_ID );

		if ( empty( $post_ID ) ) {
			$post_ID = get_the_ID();
		}

		if ( ! $post_ID ) {
			return '';
		}

		$up_count   = get_post_meta( $post_ID, '_thumbs_rating_up', true );
		$down_count = get_post_meta( $post_ID, '_thumbs_rating_down', true );

		$up_count   = ( $up_count !== '' ) ? intval( $up_count ) : 0;
		$down_count = ( $down_count !== '' ) ? intval( $down_count ) : 0;

		$up_voted_class   = $up_count > 0 ? ' thumbs-rating-voted' : '';
		$down_voted_class = $down_count > 0 ? ' thumbs-rating-voted' : '';

		// Inline SVG icons (no translations needed)
		$icon_up = '<svg class="thumbs-rating-icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M2 21h3V9H2v12zM23 10c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-1z"/></svg>';
		$icon_down = '<svg class="thumbs-rating-icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M22 3h-3v12h3V3zM1 14c0 1.1.9 2 2 2h6.31l-.95 4.57-.03.32c0 .41.17.79.44 1.06L9.83 23l6.59-6.59c.36-.36.58-.86.58-1.41V5c0-1.1-.9-2-2-2H6c-.83 0-1.54.5-1.84 1.22l-3.02 7.05c-.09.23-.14.47-.14.73v2z"/></svg>';

		$link_up = sprintf(
			'<button type="button" class="thumbs-rating-up%1$s" data-vote="up" data-post-id="%2$d" aria-label="%3$s">%4$s<span class="thumbs-rating-count">%5$d</span></button>',
			esc_attr( $up_voted_class ),
			$post_ID,
			esc_attr__( 'Vote up', 'thumbs-rating' ),
			$icon_up,
			$up_count
		);

		$link_down = sprintf(
			'<button type="button" class="thumbs-rating-down%1$s" data-vote="down" data-post-id="%2$d" aria-label="%3$s">%4$s<span class="thumbs-rating-count">%5$d</span></button>',
			esc_attr( $down_voted_class ),
			$post_ID,
			esc_attr__( 'Vote down', 'thumbs-rating' ),
			$icon_down,
			$down_count
		);

		$out  = '<div class="thumbs-rating-container" id="thumbs-rating-' . esc_attr( $post_ID ) . '" data-content-id="' . esc_attr( $post_ID ) . '">';
		$out .= $link_up;
		$out .= $link_down;
		$out .= '<span class="thumbs-rating-already-voted" aria-live="polite">' . esc_html__( 'You already voted!', 'thumbs-rating' ) . '</span>';
		$out .= '</div>';

		return $out;
	}
endif;


/*-----------------------------------------------------------------------------------*/
/* AJAX: handle a vote */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_add_vote_callback' ) ) :
	function thumbs_rating_add_vote_callback() {

		check_ajax_referer( 'thumbs-rating-nonce', 'nonce' );

		$post_ID = isset( $_POST['postid'] ) ? intval( $_POST['postid'] ) : 0;
		$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';

		if ( ! $post_ID || ! in_array( $type, array( 'up', 'down' ), true ) ) {
			wp_send_json_error( array( 'message' => 'Invalid vote.' ), 400 );
		}

		if ( ! get_post( $post_ID ) ) {
			wp_send_json_error( array( 'message' => 'Post not found.' ), 404 );
		}

		$meta_name = ( $type === 'up' ) ? '_thumbs_rating_up' : '_thumbs_rating_down';

		$current = get_post_meta( $post_ID, $meta_name, true );
		$current = ( $current !== '' ) ? intval( $current ) : 0;
		$current++;

		update_post_meta( $post_ID, $meta_name, $current );

		// Return the freshly rendered container HTML + the counts, for flexibility
		wp_send_json_success(
			array(
				'html' => thumbs_rating_getlink( $post_ID, $type ),
				'up'   => intval( get_post_meta( $post_ID, '_thumbs_rating_up', true ) ),
				'down' => intval( get_post_meta( $post_ID, '_thumbs_rating_down', true ) ),
			)
		);
	}
	add_action( 'wp_ajax_thumbs_rating_add_vote', 'thumbs_rating_add_vote_callback' );
	add_action( 'wp_ajax_nopriv_thumbs_rating_add_vote', 'thumbs_rating_add_vote_callback' );
endif;


/*-----------------------------------------------------------------------------------*/
/* Admin columns */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_columns' ) ) :
	function thumbs_rating_columns( $columns ) {
		unset( $columns['author'] );
		return array_merge(
			$columns,
			array(
				'thumbs_rating_up_count'   => __( 'Up Votes', 'thumbs-rating' ),
				'thumbs_rating_down_count' => __( 'Down Votes', 'thumbs-rating' ),
			)
		);
	}
	add_filter( 'manage_posts_columns', 'thumbs_rating_columns' );
	add_filter( 'manage_pages_columns', 'thumbs_rating_columns' );
endif;


if ( ! function_exists( 'thumbs_rating_column_values' ) ) :
	function thumbs_rating_column_values( $column, $post_id ) {
		$post_id = intval( $post_id );
		switch ( $column ) {
			case 'thumbs_rating_up_count':
				$val = get_post_meta( $post_id, '_thumbs_rating_up', true );
				echo $val !== '' ? '+' . intval( $val ) : '0';
				break;
			case 'thumbs_rating_down_count':
				$val = get_post_meta( $post_id, '_thumbs_rating_down', true );
				echo $val !== '' ? '-' . intval( $val ) : '0';
				break;
		}
	}
	add_action( 'manage_posts_custom_column', 'thumbs_rating_column_values', 10, 2 );
	add_action( 'manage_pages_custom_column', 'thumbs_rating_column_values', 10, 2 );
endif;


/*-----------------------------------------------------------------------------------*/
/* Sortable admin columns */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_sortable_columns' ) ) :
	function thumbs_rating_sortable_columns( $columns ) {
		$columns['thumbs_rating_up_count']   = 'thumbs_rating_up_count';
		$columns['thumbs_rating_down_count'] = 'thumbs_rating_down_count';
		return $columns;
	}

	add_action( 'admin_init', 'thumbs_rating_sort_all_public_post_types' );

	function thumbs_rating_sort_all_public_post_types() {
		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $post_type_name ) {
			add_action( 'manage_edit-' . $post_type_name . '_sortable_columns', 'thumbs_rating_sortable_columns' );
		}
		add_filter( 'request', 'thumbs_rating_column_sort_orderby' );
	}

	function thumbs_rating_column_sort_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'thumbs_rating_up_count' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_thumbs_rating_up',
					'orderby'  => 'meta_value_num',
				)
			);
		}
		if ( isset( $vars['orderby'] ) && 'thumbs_rating_down_count' === $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_thumbs_rating_down',
					'orderby'  => 'meta_value_num',
				)
			);
		}
		return $vars;
	}
endif;


/*-----------------------------------------------------------------------------------*/
/* Helper functions */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_show_up_votes' ) ) :
	function thumbs_rating_show_up_votes( $post_id = 0 ) {
		$post_id = $post_id ? intval( $post_id ) : get_the_ID();
		$val     = get_post_meta( $post_id, '_thumbs_rating_up', true );
		return $val !== '' ? intval( $val ) : 0;
	}
endif;

if ( ! function_exists( 'thumbs_rating_show_down_votes' ) ) :
	function thumbs_rating_show_down_votes( $post_id = 0 ) {
		$post_id = $post_id ? intval( $post_id ) : get_the_ID();
		$val     = get_post_meta( $post_id, '_thumbs_rating_down', true );
		return $val !== '' ? intval( $val ) : 0;
	}
endif;


/*-----------------------------------------------------------------------------------*/
/* Top Votes Shortcode [thumbs_rating_top] */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_top_func' ) ) :
	function thumbs_rating_top_func( $atts ) {

		$atts = shortcode_atts(
			array(
				'type'           => 'positive',
				'posts_per_page' => 5,
				'category'       => '',
				'show_votes'     => 'yes',
				'post_type'      => 'any',
				'show_both'      => 'no',
			),
			$atts,
			'thumbs_rating_top'
		);

		$type           = $atts['type'];
		$posts_per_page = intval( $atts['posts_per_page'] );
		$category       = $atts['category'];
		$show_votes     = $atts['show_votes'];
		$post_type      = $atts['post_type'];
		$show_both      = $atts['show_both'];

		if ( 'positive' === $type ) {
			$meta_key       = '_thumbs_rating_up';
			$other_meta_key = '_thumbs_rating_down';
			$sign           = '+';
			$other_sign     = '-';
		} else {
			$meta_key       = '_thumbs_rating_down';
			$other_meta_key = '_thumbs_rating_up';
			$sign           = '-';
			$other_sign     = '+';
		}

		$args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'cat'            => $category,
			'posts_per_page' => $posts_per_page,
			'no_found_rows'  => true,
			'cache_results'  => true,
			'meta_key'       => $meta_key,
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		);

		$query = new WP_Query( $args );

		$return = '';

		if ( $query->have_posts() ) :

			$return .= '<ol class="thumbs-rating-top-list">';

			while ( $query->have_posts() ) {
				$query->the_post();

				$return .= '<li>';
				$return .= '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';

				if ( 'yes' === $show_votes ) {

					$meta_values = get_post_meta( get_the_ID(), $meta_key );
					$count       = ( is_array( $meta_values ) && isset( $meta_values[0] ) ) ? intval( $meta_values[0] ) : 0;

					$return .= ' (' . esc_html( $sign . $count );

					if ( 'yes' === $show_both ) {
						$other_values = get_post_meta( get_the_ID(), $other_meta_key );
						$other_count  = ( is_array( $other_values ) && isset( $other_values[0] ) ) ? intval( $other_values[0] ) : 0;
						$return      .= ' ' . esc_html( $other_sign . $other_count );
					}

					$return .= ')';
				}

				$return .= '</li>';
			}

			$return .= '</ol>';
			wp_reset_postdata();

		endif;

		return $return;
	}
	add_shortcode( 'thumbs_rating_top', 'thumbs_rating_top_func' );
endif;


/*-----------------------------------------------------------------------------------*/
/* Shortcode for the buttons */
/*-----------------------------------------------------------------------------------*/

if ( ! function_exists( 'thumbs_rating_shortcode_func' ) ) :
	function thumbs_rating_shortcode_func( $atts ) {
		return thumbs_rating_getlink();
	}
	add_shortcode( 'thumbs-rating-buttons', 'thumbs_rating_shortcode_func' );
endif;