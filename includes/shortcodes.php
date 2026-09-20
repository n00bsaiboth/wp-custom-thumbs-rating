<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thumbs_rating_shortcode_func' ) ) :
	function thumbs_rating_shortcode_func( $atts ) {
		return thumbs_rating_getlink();
	}

	add_shortcode( 'thumbs-rating-buttons', 'thumbs_rating_shortcode_func' );
endif;

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

		$type           = ( 'negative' === strtolower( $atts['type'] ) ) ? 'negative' : 'positive';
		$posts_per_page = max( 1, absint( $atts['posts_per_page'] ) );
		$category       = absint( $atts['category'] );
		$show_votes     = strtolower( $atts['show_votes'] );
		$post_type      = sanitize_key( $atts['post_type'] );
		$show_both      = strtolower( $atts['show_both'] );

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

					$count = absint( get_post_meta( get_the_ID(), $meta_key, true ) );

					$return .= ' (' . esc_html( $sign . $count );

					if ( 'yes' === $show_both ) {
						$other_count = absint( get_post_meta( get_the_ID(), $other_meta_key, true ) );
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