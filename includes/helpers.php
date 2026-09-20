<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SVG loader
 */

if ( ! function_exists( 'thumbs_rating_get_svg' ) ) :
	function thumbs_rating_get_svg( $filename, $class = '' ) {

		static $cache = array();

		$filename = sanitize_file_name( $filename );
		$cache_key = $filename . '|' . $class;

		if ( isset( $cache[ $cache_key ] ) ) {
			return $cache[ $cache_key ];
		}

		$file = THUMBS_RATING_PATH . 'assets/images/' . $filename . '.svg';

		if ( ! file_exists( $file ) || ! is_readable( $file ) ) {
			return '';
		}

		$svg = file_get_contents( $file );

		if ( false === $svg ) {
			return '';
		}

		if ( $class !== '' ) {
			if ( preg_match( '/<svg\b[^>]*\bclass\s*=/i', $svg ) ) {
				// Append to existing class attribute
				$svg = preg_replace(
					'/(<svg\b[^>]*\bclass\s*=\s*["\'])([^"\']*)(["\'])/i',
					'$1$2 ' . esc_attr( $class ) . '$3',
					$svg,
					1
				);
			} else {
				// Insert a new class attribute
				$svg = preg_replace(
					'/<svg\b/i',
					'<svg class="' . esc_attr( $class ) . '"',
					$svg,
					1
				);
			}
		}

		$cache[ $cache_key ] = $svg;
		return $svg;
	}
endif;


/**
 * Render the thumbs up/down buttons
 */

if ( ! function_exists( 'thumbs_rating_getlink' ) ) :
	function thumbs_rating_getlink( $post_ID = 0, $type_of_vote = 0 ) {

		$post_ID = absint( $post_ID );

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

		// $up_voted_class   = $up_count > 0 ? ' thumbs-rating-voted' : '';
		// $down_voted_class = $down_count > 0 ? ' thumbs-rating-voted' : '';

		$icon_up   = thumbs_rating_get_svg( 'thumbs-up', 'thumbs-rating-icon' );
		$icon_down = thumbs_rating_get_svg( 'thumbs-down', 'thumbs-rating-icon' );

		$link_up = sprintf(
        	'<button type="button" class="thumbs-rating-up" data-vote="up" data-post-id="%1$d" aria-label="%2$s">%3$s<span class="thumbs-rating-count">%4$d</span></button>',
	        $post_ID,
	        esc_attr__( 'Vote up', 'thumbs-rating' ),
	        $icon_up,
	        $up_count
        );

		$link_down = sprintf(
	        '<button type="button" class="thumbs-rating-down" data-vote="down" data-post-id="%1$d" aria-label="%2$s">%3$s<span class="thumbs-rating-count">%4$d</span></button>',
	        $post_ID,
	        esc_attr__( 'Vote down', 'thumbs-rating' ),
	        $icon_down,
	        $down_count
        );

        $show_title = (int) get_option( 'thumbs_rating_show_title', 1 );
        $title_text = get_option( 'thumbs_rating_title', __( 'Did you find this article useful?', 'thumbs-rating' ) );

        $title_html = '';
        
        if ( $show_title && $title_text !== '' ) {
	        $title_html = '<h3 class="thumbs-rating-title">' . esc_html( $title_text ) . '</h3>';
        }

		$out  = '<div class="thumbs-rating-wrapper" data-content-id="' . esc_attr( $post_ID ) . '">';
        $out .= $title_html;
        $out .= '<div class="thumbs-rating-container" id="thumbs-rating-' . esc_attr( $post_ID ) . '">';
        $out .= $link_up;
        $out .= $link_down;
        $out .= '<span class="thumbs-rating-already-voted" aria-live="polite">' . esc_html__( 'You already voted!', 'thumbs-rating' ) . '</span>';
        $out .= '</div>'; // .thumbs-rating-container
        $out .= '</div>'; // .thumbs-rating-wrapper

		return $out;
	}
endif;


/**
 * Value helpers
 */

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